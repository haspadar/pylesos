<?php
use Dotenv\Dotenv;
use Pylesos\PylesosService;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$env = $dotenv->load();
$env['ROTATOR_URL'] = '';
$env['PROXIES'] = '';
$env['PROXY'] = '';
$response = PylesosService::download('http://api.ipify.org/', $env);
var_dump($response->getRequest()->getMotor());
var_dump($response->getResponse());
$env['MOTOR'] = 'chrome';
$response = PylesosService::download('http://api.ipify.org/', $env);
var_dump($response->getRequest()->getMotor());
var_dump($response->getResponse());