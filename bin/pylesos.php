#!/usr/bin/php
<?php

use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pylesos\Pylesos;
use Pylesos\Request;
use Pylesos\Rotator;

require_once 'paths.php';

$vendorPath = loadAutoload();
$dotenv = Dotenv::createImmutable(dirname($vendorPath));
$env = $dotenv->load();
$request = new Request($env);
$logger = new Logger('pylesos');
$logger->pushHandler(new StreamHandler('php://stdout'));
if (!$error = $request->validate()) {
    $motor = $request->generateMotor();
    if ($motor) {
        $rotator = new Rotator($request);
        $pylesos = new Pylesos($motor, $rotator, $logger);
        $response = $pylesos->download($request->getUrl());
        $response->colorize();
    } else {
        $logger->error('Motor not found');
    }

} else {
    $logger->error($error);
}