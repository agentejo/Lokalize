<?php

namespace Lokalize\Controller;


class RestApi extends \LimeExtra\Controller {


    public function project($name, $lang = null) {

        $project = $this->app->storage->findOne('lokalize/projects', [
            'name' => $name
        ]);

        if (!$project) {
            return false;
        }

        if ($lang) {
            $obj = new \ArrayObject(isset($project['values'][$lang]) ? $project['values'][$lang] : []);
        } else {
            $obj =new \ArrayObject(isset($project['values']) ? $project['values'] : []);
        }

        if (!isset($obj[$project['lang']])) {
            $obj[$project['lang']] = new \ArrayObject([]);
        }

        foreach ((array)$project['languages'] as $lang) {
            if (!isset($obj[$lang]))  $obj[$lang] = new \ArrayObject([]);
        }

        return $obj;
    }

    public function updateProject($name) {
        $values = $this->param('values');
        $project = $this->app->storage->findOne('lokalize/projects', [
            'name' => $name
        ]);

        if (!$values || !$name || !$project) {
            return false;
        }

        $_project = $this->module('lokalize')->project($project['_id']);
        $_project['values'] = array_replace_recursive($_project["values"], $values);
        $_project['_modified'] = time();

        return $this->module('lokalize')->saveProject($_project);
    }
}
