<?php
namespace App\Library\Services\Contracts;

use GuzzleHttp\HandlerStack;

interface ProxiesSourceInterface
{
    public function downloadProxies(HandlerStack $handlerStack = null): array;
}
