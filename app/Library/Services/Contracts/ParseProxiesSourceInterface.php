<?php
namespace App\Library\Services\Contracts;

use GuzzleHttp\HandlerStack;

interface ParseProxiesSourceInterface
{
    public function downloadProxies(HandlerStack $handlerStack = null): array;
}
