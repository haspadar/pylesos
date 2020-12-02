#!/usr/bin/php
<?php

use Dotenv\Dotenv;
use Pylesos\Proxies;
use Pylesos\Pylesos;
use Pylesos\Request;
use Pylesos\Rotator;
use Pylesos\Squid;

require dirname(__FILE__) . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$request = new Request();
if (!$error = $request->validate()) {
    $motor = $request->generateMotor();
    if ($motor) {
        $rotator = new Rotator($request);
        $pylesos = new Pylesos($motor, $rotator);
        $response = $pylesos->download($request->getUrl());
        $response->colorize();
    } else {
        echo 'Motor not found' . PHP_EOL;
    }

} else {
    echo $error . PHP_EOL;
}