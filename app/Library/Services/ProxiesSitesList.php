<?php

namespace App\Library\Services;

abstract class ProxiesSitesList
{
    /**
     * @var SiteWithParseProxies[]
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
