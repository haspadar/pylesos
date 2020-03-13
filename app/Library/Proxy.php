<?php
namespace App\Library;

class Proxy
{
    protected string $ipWithPort;

    protected string $protocol;

    protected int $id = 0;

    private string $login;

    private string $password;

    public function __construct(
        string $ipWithPort = '',
        string $protocol = 'http',
        string $login = '',
        string $password = ''
    ) {
        $this->ipWithPort = $ipWithPort;
        $this->protocol = strtolower($protocol);
        $this->login = $login;
        $this->password = $password;
    }

    public function getAddress(): string
    {
        return $this->ipWithPort;
    }

    /**
     * Value for CURLOPT_PROXYUSERPWD
     * @return string
     */
    public function getCurlProxyPassword(): string
    {
        return $this->login . ':' . $this->password;
    }

    /**
     * Value for CURLOPT_PROXYTYPE
     * @return string
     */
    public function getCurlProxyType(): string
    {
        if ($this->protocol == 'https') {
            $type = CURLPROXY_HTTP;
        } elseif ($this->protocol == 'socks4') {
            $type = CURLPROXY_SOCKS4;
        } elseif ($this->protocol == 'socks5') {
            $type = CURLPROXY_SOCKS5;
        } elseif ($this->protocol == 'https') {
            $type = CURLPROXY_HTTPS;
        } else {
            $type = CURLPROXY_HTTP;
        }

        return $type;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getId()
    {
        if (!$this->id) {
            if (!$found = \DB::select('SELECT id FROM proxies WHERE address = ?', [$this->getAddress()])) {
                \DB::insert('INSERT INTO proxies (address, protocol) values (?, ?)', [
                    $this->getAddress(),
                    $this->getProtocol()
                ]);
                $found = \DB::select('SELECT id FROM proxies WHERE address = ?', [$this->getAddress()]);
            }

            $this->id = $found[0]->id;
        }

        return $this->id;
    }
}
