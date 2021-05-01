<?php

namespace Pylesos;

class Proxy
{
    private string $address;

    private string $auth;

    private string $ip;

    private string $port;

    private string $login;

    private string $password;

    public function __construct(string $addressWithAuth)
    {
        if (mb_substr($addressWithAuth, 0, 4) !== 'http') {
            $addressWithAuth = 'http://' . $addressWithAuth;
        }

        $parsed = parse_url($addressWithAuth);
        if ($parsed) {
            $this->ip = $parsed['host'] ?? '';
            $this->port = $parsed['port'] ?? '';
            $this->login = $parsed['user'] ?? '';
            $this->password = $parsed['pass'] ?? '';
            $scheme = $parsed['scheme'];
            $this->address = $scheme
                . '://'
                . $this->ip
                . ':'
                . $this->port;
            $this->auth = $this->login ? $this->login . ':' . $this->password : '';
        }
    }

    public function getAddress(): string
    {
        return $this->address ?? '';
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function getAuth(): string
    {
        return $this->auth ?? '';
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}