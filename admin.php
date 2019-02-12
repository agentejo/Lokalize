<?php


$app->on('admin.init', function() {

    if (!$this->module('cockpit')->hasaccess('lokalize', 'manage')) {

        $this->bind('/lokalize/*', function() {
            return $this->helper('admin')->denyRequest();
        });

        return;
    }

    // bind admin routes /lokalize/*
    $this->bindClass('Lokalize\\Controller\\Admin', 'lokalize');

    // add to modules menu
    $this('admin')->addMenuItem('modules', [
        'label' => 'Lokalize',
        'icon'  => 'lokalize:icon.svg',
        'route' => '/lokalize',
        'active' => strpos($this['route'], '/lokalize') === 0
    ]);

});
