<?php

if (!COCKPIT_CLI) return;

$target = $app->param('target', null);

if (!$target) {
    return;
}

foreach ($app->module('lokalize')->projects() as $project) {

    $name = $project['name'];

    CLI::writeln("Exporting lokalize/{$name}");
    $app->helper('fs')->write("{$target}/lokalize/{$name}.json", json_encode($project, JSON_PRETTY_PRINT));
}
