<?php
namespace App\Library\Services\Contracts;

use GuzzleHttp\Client;

interface UserAgentsSourceInterface
{
    public function downloadUserAgents(Client $client): array;
}
