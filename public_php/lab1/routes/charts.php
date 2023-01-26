<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
    return $this -> view -> render($response, 'chart.html.twig', ['doc_title' => "Telemetry data", 'css_path' => CSS_PATH]);
})-> setName('charts');