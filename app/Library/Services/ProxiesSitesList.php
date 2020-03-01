<?php

namespace App\Library\Services;

abstract class ProxiesSitesList
{
    /**
     * @var SiteWithProxies[]
     */
    protected array $sites = [];

    /**
     * @return array
     */
    public function getSites(): array
    {
        return $this->sites;
    }
}
