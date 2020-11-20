<?php

namespace Pylesos;

class Proxies
{
    public static function getAddresses(Request $request): array
    {
        if ($request->getProxy()) {
            $addresses = [$request->getProxy()];
        } elseif ($request->getProxies()) {
            $addresses = $request->getProxies();
        } elseif ($request->getRotatorUrl()) {
            $addresses = self::getRotatorAddresses($request->getRotatorUrl());
        } else {
            $addresses = [];
        }

        if ($request->hasSquid()) {
            $squid = new Squid($addresses);

            return $squid->getAddresses();
        }

        return $addresses;
    }

    public static function generateProxies(array $addresses): array
    {
        $proxies = [];
        foreach ($addresses as $address) {
            $proxies[] = new Proxy($address);
        }

        return $proxies;
    }

    private static function getRotatorAddresses(string $apiUrl): array
    {
        $list = [];
        $response = json_decode(file_get_contents($apiUrl));
        foreach ($response->list as $rotatorProxy) {
            $list[] = $rotatorProxy->auth . '@' . $rotatorProxy->address;
        }

        return $list;
    }
}