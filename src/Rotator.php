<?php
namespace Pylesos;

class Rotator
{
    private Request $request;

    private array $proxies;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $addresses = Proxies::getAddresses($request);
        $this->proxies = Proxies::generateProxies($addresses);
    }

    public function popProxy(): ?Proxy
    {
        return $this->proxies ? array_pop($this->proxies) : null;
    }

    public function getProxies(): array
    {
        return $this->proxies;
    }
}