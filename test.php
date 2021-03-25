<?php
use Dotenv\Dotenv;
use Pylesos\PylesosService;
use Pylesos\Scheduler;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$env = $dotenv->load();
$scheduler = new Scheduler($env);
$scheduler->run(function () use ($env) {
    $response = PylesosService::download('https://tut.by', $env);
    echo $response->getResponse() . PHP_EOL;
});