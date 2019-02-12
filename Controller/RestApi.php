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

        return $obj;
    }
}
