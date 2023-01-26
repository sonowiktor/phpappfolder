<?php

include 'src/M2mResponse.php';
include 'src/InputContainer.php';
//include 'src/Authentication.php';
include 'src/KeyAuth.php';
include 'src/Message.php';
//include 'src/HtmlData.php';
include 'src/DatabaseWrapper.php';
include 'src/SessionWrapper.php';
include 'src/Monologging.php';

use Coursework\M2mResponse;
use Coursework\InputContainer;


use Coursework\KeyAuth;
use Coursework\Message;

//use Gobbwobblers\HtmlData;
use Coursework\DatabaseWrapper;
use Coursework\SessionWrapper;
use Coursework\Monologging;

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

$container['InputContainer'] = function () {
    return new Coursework\InputContainer();
};

$container['KeyAuth'] = function () {
    return new Coursework\KeyAuth();
};

$container['M2mResponse'] = function () {
    return new Coursework\M2mResponse();
};

$container['Message'] = function () {
    return new Coursework\Message();
};

$container['SessionWrapper'] = function () {
    return new Coursework\SessionWrapper();
};

$container['Logger'] = function () {
    $logger = new Logger('logger');

    $log_notices = LOG_FILE_PATH . 'notices.log';
    $stream_notices = new StreamHandler($log_notices, Logger::NOTICE);
    $logger->pushHandler($stream_notices);

    $log_warnings = LOG_FILE_PATH . 'warnings.log';
    $stream_warnings = new StreamHandler($log_warnings, Logger::WARNING);
    $logger->pushHandler($stream_warnings);

    return $logger;
};