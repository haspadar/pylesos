<?php

namespace App\Library\Proxy\Adapters;

use App\Library\Proxy;

class FreeProxySite extends AdapterAbstract
{
    /**
     * @param array $options
     * @return Proxy[] array
     */
    public static function getProxies(array $options = []): array
    {
        $rows = \DB::select('SELECT * FROM proxies WHERE adapter = :adapter', [
            'adapter' => self::getAdapterName()
        ]);
        $proxies = [new Proxy()];
        foreach ($rows as $row) {
            $proxies[] = new Proxy($row->address, $row->protocol);
        }

        return $proxies;
    }
}
