<?php

include(__DIR__.'/vendor/autoload.php');

$this->module("lokalize")->extend([

    'languages' => function() {

        static $languages;

        if (is_null($languages)) {

            $languages = [
                'af' => 'Afrikaans',
                'ar' => 'Arabic',
                'bs' => 'Bosnian',
                'zh' => 'Chinese',
                'hr' => 'Croatian',
                'da' => 'Danish',
                'nl' => 'Dutch',
                'en' => 'English',
                'fi' => 'Finnish',
                'fr' => 'French',
                'de' => 'German',
                'he' => 'Hebrew',
                'hi' => 'Hindi',
                'hu' => 'Hungarian',
                'is' => 'Icelandic',
                'it' => 'Italian',
                'id' => 'Indonesian',
                'ja' => 'Japanese',
                'no' => 'Norwegian',
                'pl' => 'Polish',
                'pt' => 'Portuguese',
                'ru' => 'Russian',
                'es' => 'Spanish',
                'sv' => 'Swedish',
                'tr' => 'Turkish',
            ];

            foreach ($this->app->retrieve('config/languages', []) as $code => $label) {

                if (!isset($languages[$code])) {
                    $languages[$code] = $label;
                }
            }

            foreach ($this->app->retrieve('config/lokalize/languages', []) as $code => $label) {

                if (!isset($languages[$code])) {
                    $languages[$code] = $label;
                }
            }
        }

        return $languages;
    },

    'projects' => function() {

        return $this->app->storage->find('lokalize/projects', [
            'sort' => ['name' => 1]
        ])->toArray();
    },

    'project' => function($id) {

        $project = $this->app->storage->findOne('lokalize/projects', [
            '_id' => $id
        ]);

        if ($project && $this->app->retrieve('config/lokalize/persistKeys') && $keyspath = $this->app->path("#storage:lokalize/keys/{$project['name']}.php")) {
            $project['keys'] = array_merge(include($keyspath), $project['keys']);
        }

        return $project;
    },

    'saveProject' => function($project) {

        $this->app->storage->save('lokalize/projects', $project);

        if ($this->app->retrieve('config/lokalize/persistKeys')) {
            $this->app->helper('fs')->write('#storage:lokalize/keys/'.$project['name'].'.php', "<?php\n return ".var_export($project['keys'], true).";");
        }

        return $project;
    },

    'removeProject' => function($project) {

        $id = is_string($project) ? $project : $project['_id'];

        if ($this->app->retrieve('config/lokalize/persistKeys') && isset($project['name'])) {
            $this->app->helper('fs')->delete('#storage:lokalize/keys/'.$project['name'].'.php');
        }

        return $this->app->storage->remove('lokalize/projects', ['_id' => $id]);
    }
]);

// CLI
if (COCKPIT_CLI) {
    $this->path('#cli', __DIR__.'/cli');
}


// ACL
$app('acl')->addResource('lokalize', ['manage']);


// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {

    include_once(__DIR__.'/admin.php');
}

// REST
if (COCKPIT_API_REQUEST) {

    $app->on('cockpit.rest.init', function($routes) {
        $routes['lokalize'] = 'Lokalize\\Controller\\RestApi';
    });

    // allow access to public collections
    $app->on('cockpit.api.authenticate', function($data) {

        if ($data['user'] || $data['resource'] != 'lokalize') return;

        if ($this->retrieve('config/lokalize/publicAccess', false)) {
            $data['authenticated'] = true;
            $data['user'] = ['_id' => null, 'group' => 'public'];
        }
    });
}
