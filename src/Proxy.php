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
        $parsed = parse_url($addressWithAuth);
        $this->ip = $parsed['host'] ?? '';
        $this->port = $parsed['port'] ?? '';
        $this->login = $parsed['user'] ?? '';
        $this->password = $parsed['pass'] ?? '';
        $scheme = $parsed['scheme'] ?? 'http';
        $this->address = $scheme
            . '://'
            . $this->ip
            . ':'
            . $this->port;
        $this->auth = $this->login ? $this->login . ':' . $this->password : '';

//        list($auth, $address) = explode('@', $addressWithAuth);
//        $this->address = $address;
//        list($ip, $port) = explode(':', $this->address);
//        $this->ip = $ip;
//        $this->port = $port;
//        $this->auth = $auth;
//        list($login, $password) = explode(':', $this->auth);
//        $this->login = $login;
//        $this->password = $password;
    }

    public function getAddress(): string
    {
        return $this->address;
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
        return $this->auth;
    }
}