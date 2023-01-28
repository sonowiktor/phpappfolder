<?php

/**
 *this logs all the information
*Notice for the general log for all other needs
 *alerts created when the login information is incorrect when entered
 *information generated when correct login details used - also to be recorded
 * error to be presented when issues with the incorrect logType passed
 */

namespace Coursework;

use Coursework\Logger;
use Coursework\Handler\StreamHandler;

class monolog
{
    function __construct() {}

    function __destruct() {}

    public function log($logType, $error)
    {

        //Notice
        $logger = new Logger('logger');
        if ($logType == 'Notice') {
            $log_notices = LOG_FILE_PATH . 'notices.log';
            $stream_notices = new StreamHandler($log_notices, Logger::NOTICE);
            $logger->pushHandler($stream_notices);
            $logger->notice($error);
        }
        //Alert
        elseif ($logType == 'Alert') {
            $log_alerts = LOG_FILE_PATH . 'alerts.log';
            $stream_alerts = new StreamHandler($log_alerts, Logger::ALERT);
            $logger->pushHandler($stream_alerts);
            $logger->alert($error);
        }
        //Info
        elseif ($logType == 'Info') {
            $log_info = LOG_FILE_PATH . 'info.log';
            $stream_info = new StreamHandler($log_info, Logger::INFO);
            $logger->pushHandler($stream_info);
            $logger->info($error);
        }

        //Errors
        else {
            $log_error = LOG_FILE_PATH . 'errors.log';
            $stream_notices = new StreamHandler($log_error, Logger::NOTICE);
            $logger->pushHandler($stream_notices);
            $logger->error('Error logType Mismatch, ' . $logType . ' was Entered!');
        }
    }
}