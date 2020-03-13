<?php

namespace App\Library\Proxy\Adapters;

use App\Library\Proxy;

class ProxySaleCom extends AdapterAbstract
{
    const DEFAULT_PORT = '65233';

    const DEFAULT_PROTOCOL = 'https';

    /**
     * @param array $options
     * @return Proxy[] array
     */
    public static function getProxies(array $options = []): array
    {
        $proxies = [];
        if (isset($options['PROXY_SALE_COM_PROXY_IPS'])) {
            $ips = array_filter(explode(',', $options['PROXY_SALE_COM_PROXY_IPS']), function ($ip) {
                return strlen(trim($ip)) > 0;
            });
            $port = $options['PROXY_SALE_COM_PROXY_PORT'] ?? self::DEFAULT_PORT;
            $protocol = $options['PROXY_SALE_COM_PROXY_PROTOCOL'] ?? self::DEFAULT_PROTOCOL;
            $login = $options['PROXY_SALE_COM_PROXY_LOGIN'] ?? '';
            $password = $options['PROXY_SALE_COM_PROXY_PASSWORD'] ?? '';
            foreach ($ips as $ip) {
                $proxies[] = new Proxy(
                    $ip . ':' . $port,
                    $protocol,
                    $login,
                    $password
                );
            }
        }

        return $proxies;
    }
}
