<?php
namespace App\Library\Proxy\Adapters\Contracts;

interface AdapterInterface
{
    public static function getProxies(array $options = []): array;

    public static function getAdapterName(): string;
}
