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

        if (isset($project['_id']) && isset($project['_modified'])) {
            
            $_project = $this->module('lokalize')->project($project['_id']);

            if ($_project['_modified'] > $project['_modified']) {
                $project = array_replace_recursive($_project, $project);
            }
        }

        $project['_modified'] = time();

        $this->app->trigger('lokalize.saveproject', [&$project]);

        return $this->module('lokalize')->saveProject($project);
    }

    public function project($projectId = null) {

        $project = $this->module('lokalize')->project($projectId);

        if (!$project) {
            return false;
        }

        if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($projectId, $meta)) {
            return $this->render('cockpit:views/base/locked.php', compact('meta'));
        }

        $this->app->helper('admin')->lockResourceId($projectId);

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
        $rows      = [array_merge(['key','info'], $languages)];

        // setup csv writer
        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setOutputBOM(\League\Csv\Reader::BOM_UTF8);
        $csv->setDelimiter($this->app->retrieve('config/lokalize/delimiter', ';'));
        $csv->setNewline("\r\n");

        foreach ($project['keys'] as $key => $meta) {

            $row = [$key, (is_string($meta) ? $meta : $meta['info'])];

            foreach ($languages as &$lang) {
                $value = '';

                if (isset($project['values'][$lang][$key])) {
                    $value = $project['values'][$lang][$key];
                }

                $row[] = $value;

            }

            $rows[] = $row;
        }

        $csv->insertAll($rows);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$this->app->helper('utils')->sluggify($project['name']).'.csv"');
        $csv->output();
        $this->app->stop();
    }


    public function import($projectId) {

        $project    = $this->module('lokalize')->project($projectId);
        $createKeys = $this->app->retrieve('config/lokalize/importkeys', false);
        $delimiter  = $this->app->retrieve('config/lokalize/delimiter', ';');

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

        $reader = \League\Csv\Reader::createFromPath($file['tmp_name'], 'r');
        $reader->setDelimiter($delimiter);
        $reader->setOutputBOM($reader->getInputBOM());

        $header = explode($delimiter, trim(str_replace($reader->getInputBOM(), '', $content[0])));

        if ($header[0] != 'key') {
            return false;
        }

        $save = false;
        $languages = array_merge([$project['lang']], $project['languages']);

        $reader->setHeaderOffset(0);

        foreach ($reader->getRecords() as $index => $row) {

            if (!$row) continue;

            $key = $row['key'];

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

                    $project['keys'][$key]['info'] = trim($row['info']);

                } elseif (in_array($lang, $languages)) {

                    $value = $row[$lang];

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
