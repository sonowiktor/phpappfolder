<?php

include 'src/m2mResponse.php';
include 'src/inputContainer.php';
include 'src/Auth.php';
include 'src/message.php';
include 'src/Database.php';
include 'src/session.php';
include 'src/monolog.php';

use Coursework\m2mResponse;
use Coursework\inputContainer;
use Coursework\Auth;
use Coursework\Message;
use Coursework\Database;
use Coursework\session;
use Coursework\monolog;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
//use Monolog\Handler\FingersCrossedHandler;


$container['logger'] = function ($container) {
    $logger = new Monolog\Logger('logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);

    return $logger;
};



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

$container['Database'] = function () {
    return new Coursework\Database();
};

$container['inputContainer'] = function () {
    return new Coursework\inputContainer();
};

$container['Auth'] = function () {
    return new Coursework\Auth();
};

$container['m2mResponse'] = function () {
    return new Coursework\m2mResponse();
};

$container['Message'] = function () {
    return new Coursework\Message();
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
