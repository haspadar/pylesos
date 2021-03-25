#!/usr/bin/php
<?php

use Dotenv\Dotenv;
use Pylesos\Pylesos;
use Pylesos\Request;
use Pylesos\Rotator;

require_once 'paths.php';

$vendorPath = loadAutoload();
$dotenv = Dotenv::createImmutable(dirname($vendorPath));
$env = $dotenv->load();
$request = new Request($env);
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