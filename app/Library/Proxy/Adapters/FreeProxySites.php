<?php

namespace App\Library\Proxy\Adapters;

use App\Library\Services\SiteWithParseProxies;

class FreeProxySites extends AdapterAbstract
{
    public static function getProxies(array $options = []): array
    {
        $classNames = [
            \App\Library\Services\FreeProxyCz::class,
            \App\Library\Services\GetproxylistCom::class,
            \App\Library\Services\ProxyListDownload::class,
        ];
        $proxies = [];
        /**
         * @var SiteWithParseProxies $siteWithProxies
         */
        foreach ($classNames as $className) {
            $siteWithProxies = new $className();
            $proxies = array_merge($proxies, $siteWithProxies->getProxies());
        }

        return $proxies;
    }

    public static function getServicesFiles(): array
    {
        $files = [];

        if ($handle = opendir('./../..')) {
            while (false !== ($entry = readdir($handle))) {
                var_dump($entry);
                if ($entry[0] != ".") {
                    $files[] = $entry;
                }
            }

            closedir($handle);
        }

        return $files;
    }
}
