<?php

ini_set('display_errors', 'On');
ini_set('html_errors', 'On');
ini_set('xdebug.trace_output_name', 'trace_coursework.%t');
ini_set('xdebug.trace_format', '1');

define('DIRSEP', DIRECTORY_SEPARATOR);

$app_url = dirname($_SERVER['SCRIPT_NAME']);
$css_path = $app_url . '/css/standard.css';
$log_Path = '../../logs/';

define('CSS_PATH', $css_path);
define('APP_NAME', 'Coursework');
define('LANDING_PAGE', $_SERVER['SCRIPT_NAME']);
define('LOG_FILE_PATH', $log_Path);

define ('WSDL', 'https://m2mconnect.ee.co.uk/orange-soap/services/MessageServiceByCountry?wsdl');

$settings = [
    "settings" => [
        'displayErrorDetails' => false,
        'addContentLengthHeader' => false,
        'mode' => 'development',
        'debug' => true,
        'class_path' => __DIR__ . '/src/',
        'view' => [
            'template_path' => __DIR__ . '/templates/',
            'twig' => [
                'cache' => false,
                'auto_reload' => true,
            ]],
        'pdo_settings' => [
            'rdbms' => 'mysql',
            'host' => 'localhost',
            'db_name' => 'p2602600db',
            'port' => '3306',
            'user_name' => 'root',
            'user_password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => true,
            ],
        ],
        'm2m_settings' => [
            'username' => '',
            'password' => '',
            'count' => 500,
            'deviceMSISDN' => null
        ]
    ],
];

$GLOBALS['settings'] = $settings;