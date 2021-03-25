<?php
use Dotenv\Dotenv;
use Pylesos\PylesosService;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$env = $dotenv->load();
$response = PylesosService::download('https://tut.by', $env);
echo $response->getResponse() . PHP_EOL;
