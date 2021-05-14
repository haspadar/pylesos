<?php
namespace Pylesos;

use Monolog\Logger;

interface MotorInterface
{
    public function __construct(Request $request);

    public function download(string $url, Rotator $rotator, array $postParams, Logger $logger): Response;
}
