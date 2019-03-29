<?php


if (!COCKPIT_CLI) return;

CLI::writeln('Flushing lokalize data');


foreach ($app->module('lokalize')->projects() as $project) {

    $app->module('lokalize')->removeProject($project);
}
