<?php
namespace App\Library\Services\Contracts;

use GuzzleHttp\HandlerStack;

interface ParseUserAgentsSourceInterface
{
    public function downloadUserAgents(HandlerStack $handlerStack = null): array;
}
