<?php

if (!COCKPIT_CLI) return;


$src = $app->param('src', null);

if (!$src) {
    return;
}

$fs = $app->helper('fs');
$projects = $fs->ls("{$src}/lokalize");
$check = $app->param('check', false);

if (count($projects)) {

    foreach ($projects as $__file) {

        $name = $__file->getBasename('.json');
        $data = $fs->read($__file->getRealPath());

        if ($project = json_decode($data, true)) {

            CLI::writeln("Importing lokalize/{$name}");

            if ($check) {

                if (!$app->storage->count('lokalize/projects', ['_id' => $project['_id']])) {
                    $app->storage->insert('lokalize/projects', $project);
                }

            } else {
                $app->storage->insert('lokalize/projects', $project);
            }
        }

    }
}
