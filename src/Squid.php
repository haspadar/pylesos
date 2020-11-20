<?php
namespace Pylesos;

class Squid
{
    private array $squidAddresses;

    private array $proxiesAddresses;

    public function __construct(array $proxiesAddresses)
    {
        $this->proxiesAddresses = $proxiesAddresses;
        if ($this->proxiesAddresses) {
            $this->updateConfig();
        }
    }

    public function getAddresses(): array
    {
        return $this->squidAddresses;
    }

    private function updateConfig()
    {
        $config = $this->getPattern();
        $firstSquidPort = 8080;
        foreach ($this->proxiesAddresses as $key => $proxyAddress) {
            $proxy = new Proxy($proxyAddress);
            $config .= 'acl port_' . ($key + 1) . ' localport ' . ($firstSquidPort + $key) . PHP_EOL;
            $config .= 'http_port ' . ($firstSquidPort + $key) . PHP_EOL;
            $config .= 'cache_peer '
                . $proxy->getIp()
                . ' parent '
                . $proxy->getPort()
                . ' 0 no-query default login='
                . $proxy->getAuth()
                . ' name=host_'
                . ($key + 1)
                . PHP_EOL;
            $config .= 'cache_peer_access host_'
                . ($key + 1)
                . ' allow port_'
                . ($key + 1)
                . PHP_EOL
                . PHP_EOL;
            $this->squidAddresses = ['http://localhost:' . ($firstSquidPort + $key)];
        }

        `pkill -9 squid`;
        file_put_contents('/etc/squid/squid.conf', $config);
        `squid -f /etc/squid/squid.conf`;
        sleep(1);
    }

    private function getPattern()
    {
        return "coredump_dir /var/spool/squid3
refresh_pattern ^ftp:       1440    20% 10080
refresh_pattern ^gopher:    1440    0%  1440
refresh_pattern -i (/cgi-bin/|\?) 0 0%  0
refresh_pattern (Release|Packages(.gz)*)$      0       20%     2880
refresh_pattern .       0   20% 4320

http_access allow all
never_direct allow all

";
    }
}