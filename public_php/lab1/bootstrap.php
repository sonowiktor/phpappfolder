<?php

session_start();

$GLOBALS['databasespoof'] = false;

require 'vendor/autoload.php';

$settings = require __DIR__ .'/settings.php';

$container = new \Slim\Container($settings);

require __DIR__ . '/dependencies.php';

$app = new Slim\App($container);

require __DIR__ . '/routes.php';

$app->run();