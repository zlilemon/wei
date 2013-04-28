<?php

use Widget\Widget;

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

require dirname(__DIR__) . '/lib/Widget/Widget.php';

return Widget::create(array(
    'debug' => true,
    'widget' => array(
        'inis' => array(
            // Display all error message
            'error_reporting' => -1,
        ),
        // Set up autoload for WidgetTest namespace
        'autoloadMap' => array(
            'WidgetTest' => __DIR__
        )
    ),
    // Databse Widget Configuration
    'db' => array(
        'driver' => 'pdo_sqlite',
        'path' => 'test.sqlite'
    ),
    // Doctrine ORM Widget Configuration
    'entityManager' => array(
        'config' => array(
            'proxyDir' => './',
            'annotationDriverPaths' => array('./')
        )
    )
));