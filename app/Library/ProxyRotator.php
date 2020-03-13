<?php
namespace App\Library;

class ProxyRotator
{
    use Rotator;

    public function getProxy(): ?Proxy
    {
        return $this->getRow() ?? null;
    }
}
