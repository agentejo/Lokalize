<?php

namespace Lokalize\Controller;


class Admin extends \Cockpit\AuthController {

    public function index() {

        $projects = $this->module('lokalize')->projects();

        return $this->render('lokalize:views/index.php', compact('projects'));
    }

    public function _project($projectId = null) {

        $project = [
            'name' => '',
            'lang' => 'en',
            'languages' => [],
            'keys'=> [],
            'values' => null,
            'color' => '',
            'done' => null,
            'acl' => new \ArrayObject,
        ];

        if ($projectId) {

            $project = $this->module('lokalize')->project($projectId);

            if (!$project) {
                return false;
            }
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper("acl")->getGroups() as $group => $superAdmin) {
            if (!$superAdmin) $aclgroups[] = $group;
        }

        return $this->render('lokalize:views/_project.php', compact('project', 'aclgroups'));
    }

    public function save_project() {

        $project = $this->param('project');

        if (!$project) {
            return false;
        }

        $this->app->trigger('lokalize.saveproject', [&$project]);

        return $this->module('lokalize')->saveProject($project);
    }

    public function project($projectId = null) {

        $project = $this->module('lokalize')->project($projectId);

        if (!$project) {
            return false;
        }

        $project['keys'] = new \ArrayObject($project['keys']);

        $languages = $this->module('lokalize')->languages();

        return $this->render('lokalize:views/project.php', compact('project', 'languages'));
    }

    public function export($projectId) {

        $project = $this->module('lokalize')->project($projectId);

        if (!$project) {
            return false;
        }

        $languages = array_merge([$project['lang']], $project['languages']);
        $rows      = ['key;info;'.implode(';', $languages)];

        foreach ($project['keys'] as $key => $meta) {

            $row = [$key, (is_string($meta) ? $meta : $meta['info'])];

            foreach ($languages as &$lang) {
                $value = '';

                if (isset($project['values'][$lang][$key])) {
                    $value = $project['values'][$lang][$key];
                }

                $row[] = $value;

            }

            $rows[] = implode(';', $row);
        }

        return implode("\n", $rows);
    }


    public function import($projectId) {

        $project    = $this->module('lokalize')->project($projectId);
        $createKeys = $this->app->retrieve('config/lokalize/importkeys', false);
        $delimiter  = ';';

        if (!$project) {
            return false;
        }

        $file = isset($_FILES['file']) ? $_FILES['file'] : null;

        if (!$file) {
            return false;
        }

        $content = file($file['tmp_name']);

        if (count($content) < 2) {
            return false;
        }

        if (strpos($content[0], $delimiter)===false) {
            $delimiter = ',';
        }

        $header = explode($delimiter, trim($content[0]));

        if ($header[0] != 'key') {
            return false;
        }

        $save = false;
        $languages = array_merge([$project['lang']], $project['languages']);

        for ($i=1; $i < count($content); $i++) {

            $row = trim($content[$i]);

            if (!$row) continue;

            $row = str_getcsv($row, $delimiter);
            $key = $row[0];

            if (!isset($project['keys'][$key])) {

                if ($createKeys) {
                    $project['keys'][$key] = ['info' => ''];
                } else {
                    continue;
                }
            }

            for ($l=1;$l<count($header);$l++) {

                $lang = trim($header[$l]);

                if ($lang == 'info' ) {

                    $project['keys'][$key]['info'] = trim($row[$l]);

                } elseif (in_array($lang, $languages)) {

                    $value = $row[$l];

                    if ($value) {

                        if (!$project['values']) $project['values'] = [];
                        if (!isset($project['values'][$lang])) $project['values'][$lang] = [];

                        $project['values'][$lang][$key] = $value;
                        $save = true;
                    }

                }
            }
        }

        if ($save) {
            $project = $this->module('lokalize')->saveProject($project);
        }

        return $project;
    }

    public function getFlagIcon($code = null) {

        if (!$code) {
            $code = 'unknown';
        }

        $code = str_replace(['..', '/'], '', $code);
        $path = $this->app->path("lokalize:assets/media/flags/{$code}.svg");

        if (!$path) {
            $path = $this->app->path("lokalize:assets/media/flags/default.svg");
        }

        $this->app->response->mime = 'svg';

        return file_get_contents($path);
    }

}
