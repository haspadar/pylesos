<?php
use Dotenv\Dotenv;
use Pylesos\PylesosService;
use Pylesos\Scheduler;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$env = $dotenv->load();
$scheduler = new Scheduler($env);
$scheduler->run(function () use ($env) {
    $response = PylesosService::get('http://api.ipify.org/', [], $env);
    echo $response->getResponse() . PHP_EOL;
});