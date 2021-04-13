<?php
use Dotenv\Dotenv;
use Pylesos\PylesosService;
use Pylesos\Request;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$env = $dotenv->load();
$env[strtoupper(Request::ROTATOR_URL)] = '';
$env[strtoupper(Request::PROXIES)] = '';
$env[strtoupper(Request::PROXY)] = '';
$response = PylesosService::download('http://api.ipify.org/', $env);
var_dump($response->getRequest()->getMotor());
var_dump($response->getResponse());
$env[strtoupper(Request::MOTOR)] = Request::MOTOR_CHROME;
$response = PylesosService::download('http://api.ipify.org/', $env);
var_dump($response->getRequest()->getMotor());
var_dump($response->getResponse());