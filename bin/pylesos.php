#!/usr/bin/php
<?php

use Dotenv\Dotenv;
use Pylesos\Pylesos;
use Pylesos\Request;
use Pylesos\Rotator;

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

function loadAutoload(): string {
    $vendorPaths = [
        dirname(dirname(__FILE__)) . '/vendor',
        dirname(dirname(dirname(dirname(__FILE__))))
    ];
    foreach ($vendorPaths as $vendorPath) {
        if (file_exists($vendorPath . '/autoload.php')) {
            require_once $vendorPath . '/autoload.php';

            return $vendorPath;
        }
    }

    return '';
}

function isVendorDirectory(string $directory): bool {
    $parts = explode('/', $directory);

    return $parts[count($parts) - 1] == 'vendor';
}

function getVendorDirectory(): string {
    $dynamicPath = dirname(__FILE__);
    if (isVendorDirectory($dynamicPath . '/vendor')) {
        return $dynamicPath . '/vendor';
    }

    while (!isVendorDirectory($dynamicPath) && $dynamicPath != '/') {
        $dynamicPath = dirname($dynamicPath);
        var_dump($dynamicPath);
    }
}