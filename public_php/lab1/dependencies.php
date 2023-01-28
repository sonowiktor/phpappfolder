<?php

include 'src/m2mResponse.php';
include 'src/inputContainer.php';
//include 'src/Authentication.php';
include 'src/KeyAuth.php';
include 'src/message.php';
//include 'src/HtmlData.php';
include 'src/DatabaseWrapper.php';
include 'src/session.php';
include 'src/monolog.php';

use Coursework\m2mResponse;
use Coursework\inputContainer;


use Coursework\KeyAuth;
use Coursework\Message;

//use Coursework\HtmlData;
use Coursework\DatabaseWrapper;
use Coursework\session;
use Coursework\monolog;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(
        $container['settings']['view']['template_path'],
        $container['settings']['view']['twig'],
        [
            'debug' => true // This line should enable debug mode
        ]
    );

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$container['DatabaseWrapper'] = function () {
    return new Coursework\DatabaseWrapper();
};

$container['inputContainer'] = function () {
    return new Coursework\inputContainer();
};

$container['KeyAuth'] = function () {
    return new Coursework\KeyAuth();
};

$container['m2mResponse'] = function () {
    return new Coursework\m2mResponse();
};

$container['message'] = function () {
    return new Coursework\message();
};

$container['session'] = function () {
    return new Coursework\session();
};

$container['logger'] = function () {
    $logger = new Logger('logger');

    $log_notices = LOG_FILE_PATH . 'notices.log';
    $stream_notices = new StreamHandler($log_notices, Logger::NOTICE);
    $logger->pushHandler($stream_notices);

    $log_warnings = LOG_FILE_PATH . 'warnings.log';
    $stream_warnings = new StreamHandler($log_warnings, Logger::WARNING);
    $logger->pushHandler($stream_warnings);
};

$container['xmlParser'] = function ($container) {
    $model = new \lab1\XmlParser();
    return $model;
    return $logger;

};
