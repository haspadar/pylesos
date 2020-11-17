<?php

namespace Pylesos;

class Proxy
{
    private string $address;
    private string $auth;

    public function __construct(string $address, string $auth)
    {
        $this->address = $address;
        $this->auth = $auth;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getAuth(): string
    {
        return $this->auth;
    }
}