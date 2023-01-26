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

use Gobbwobblers\M2mResponse;
use Gobbwobblers\InputContainer;

//use Gobbwobblers\Authentication;
use Gobbwobblers\KeyAuth;
use Gobbwobblers\Message;

//use Gobbwobblers\HtmlData;
use Gobbwobblers\DatabaseWrapper;
use Gobbwobblers\SessionWrapper;
use Gobbwobblers\Monologging;

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
    return new Gobbwobblers\DatabaseWrapper();
};

$container['InputContainer'] = function () {
    return new Gobbwobblers\InputContainer();
};

$container['KeyAuth'] = function () {
    return new Gobbwobblers\KeyAuth();
};

$container['M2mResponse'] = function () {
    return new Gobbwobblers\M2mResponse();
};

$container['Message'] = function () {
    return new Gobbwobblers\Message();
};

$container['SessionWrapper'] = function () {
    return new Gobbwobblers\SessionWrapper();
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