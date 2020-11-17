<?php
namespace Pylesos;

class Rotator
{
    private Request $request;

    private array $proxies;

    public function __construct(Request $request)
    {
        $this->request = $request;
        if ($request->getProxyAddress()) {
            $this->proxies = [new Proxy($request->getProxyAddress(), $request->getProxyAuth())];
        } elseif ($request->getRotatorUrl()) {
            $this->proxies = $this->getList($request->getRotatorUrl());
        } elseif ($request->getRotatorProxies()) {
            $this->proxies = $this->generateProxies($request->getRotatorProxies());
        }
    }

    public function popProxy(): ?Proxy
    {
        return $this->proxies ? array_pop($this->proxies) : null;
    }

    private function getList(string $rotatorUrl): array
    {
        $response = json_decode(file_get_contents($rotatorUrl));
        $list = [];
        foreach ($response->list as $rotatorProxy) {
            $list[] = new Proxy($rotatorProxy->address, $rotatorProxy->auth);
        }

        return $list;
    }

    private function generateProxies(array $requestProxies): array
    {
        $proxies = [];
        foreach ($requestProxies as $proxy) {
            list($auth, $address) = explode('@', trim($proxy));
            $proxies[] = new Proxy($address, $auth);
        }

        return $proxies;
    }
}