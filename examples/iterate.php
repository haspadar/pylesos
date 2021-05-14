<?php
use Dotenv\Dotenv;
use Pylesos\PylesosService;
use Pylesos\Request;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$env = $dotenv->load();
$env[Request::ROTATOR_URL] = '';
$env[Request::PROXIES] = '';
$env[Request::PROXY] = '';
$response = PylesosService::get('http://api.ipify.org/', [], $env);
var_dump($response->getRequest()->getMotor());
var_dump($response->getResponse());
$env[Request::MOTOR] = Request::MOTOR_CHROME;
$response = PylesosService::get('http://api.ipify.org/', [], $env);
var_dump($response->getRequest()->getMotor());
var_dump($response->getResponse());