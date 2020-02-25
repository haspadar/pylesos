<?php
namespace App\Library\Services\Contracts;

use GuzzleHttp\HandlerStack;

interface UserAgentsSourceInterface
{
    public function downloadUserAgents(HandlerStack $handlerStack = null): array;
}
