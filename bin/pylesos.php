#!/usr/bin/php
<?php

use Dotenv\Dotenv;
use Pylesos\Proxies;
use Pylesos\Pylesos;
use Pylesos\Request;
use Pylesos\Rotator;
use Pylesos\Squid;

loadAutoload();
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
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

function loadAutoload(): void {
    $dynamicPath = dirname(__FILE__);
    while (!isVendorDirectory($dynamicPath)) {
        $dynamicPath = dirname($dynamicPath);
    }

    require_once $dynamicPath . '/autoload.php';
}

function isVendorDirectory(string $directory): bool {
    $parts = explode('/', $directory);

    return $parts[count($parts) - 1] == 'vendor';
}