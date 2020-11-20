<?php
namespace Pylesos;

class Squid
{
    private array $squidAddresses;

    private array $proxiesAddresses;

    public function __construct(array $squidAddresses, array $proxiesAddresses)
    {
        $this->squidAddresses = $squidAddresses;
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
        $configPattern = file_get_contents(dirname(__DIR__) . '/sources/squid.conf');
        foreach ($this->proxiesAddresses as $proxyAddress) {

        }

        $this->squidAddresses = [];
    }
}