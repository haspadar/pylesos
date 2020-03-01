<?php

namespace App\Library\Services;

class EnvProxiesSitesList extends ProxiesSitesList
{
    public function __construct()
    {
        $classNames = array_filter(explode(',', $_ENV['PROXIES_SITES'] ?? ''));
        foreach ($classNames as $className) {
            $fullClassName = 'App\Library\Services\\' . $className;
            $this->sites[] = new $fullClassName();
        }
    }
}
