<?php
namespace App\Library\Services\Contracts;

use GuzzleHttp\Client;

interface ProxiesSourceInterface
{
    public function downloadProxies(Client $client): array;
}
