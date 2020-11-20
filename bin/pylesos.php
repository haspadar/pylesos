#!/usr/bin/php
<?php

use Dotenv\Dotenv;
use Pylesos\Pylesos;
use Pylesos\Request;
use Pylesos\Rotator;
use Pylesos\Squid;

require dirname(__FILE__) . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$request = new Request($_ENV);
if (!$error = $request->validate()) {
    $pylesos = new Pylesos($request->generateMotor());
    $response = $pylesos->download($request->getUrl(), new Rotator($request), new Squid($request));
    $response->colorize();
} else {
    echo $error . PHP_EOL;
}