<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $request, Response $response)
{
    $loginStatus = false; $userData = ""; $emptyDatabase = true;

    $conn = new Coursework\Database();
    $conn->setProcedure('getAllMessages');
    $databaseResults = $conn->execute();
    //$databaseResults = [];
    if (isset($databaseResults) && count($databaseResults) > 0) {
        //do something if there is data in database (or nothing)
        $emptyDatabase = false;
    } else {
        //spoofDatabase information
        $loginStatus = true;
        $date = date(DATE_ATOM);
        $userData = [
            'id' => '1', 'username' => 'OfflineUser', 'email' => 'user@offline.com', 'lastLogin' => $date
        ];
        //adds some spoof data to the homepage m2m results (uncomment next line, and comment the 2 proceding lines to remove all m2m results, for checking "database empty" type error)
        //$databaseResults = [];
        $emptyDatabase = false;
        $databaseResults = [
            ['id' => '0000000001', 'timestamp' => '2021-01-16 04:17:50', 'phonenumber' => '2147483647', 'heater1' => '15', 'fan1' => '0', 'name' => 'lewis', 'email' => 'lewis@lewis.com', 'sw1' => '0', 'sw2' => '1', 'sw3' => '0', 'sw4' => '1', 'keypad' => '1234'],
            ['id' => '0000000002', 'timestamp' => '2021-01-16 04:17:50', 'phonenumber' => '2147483647', 'heater1' => '15', 'fan1' => '0', 'name' => 'test', 'email' => 'test@test.com', 'sw1' => '0', 'sw2' => '1', 'sw3' => '0', 'sw4' => '1', 'keypad' => '1234']
        ];
    }

    $m2m = new Coursework\m2mresponse();

    return $this->view->render($response,
        'generatemessage.html.twig',
        [
            'document_title' => "Telemetry Data",
            'css_path' => CSS_PATH,
            'title' => "Telemetry Data",
            'author' => "23-3110-AI",
            'login_status' => $loginStatus,
            'array' => $databaseResults,
            'user' => $userData,
            'emptyDatabase' => $emptyDatabase
        ]);

})->setName('generatemessage');