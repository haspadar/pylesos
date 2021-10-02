<?php
namespace Pylesos;

use Monolog\Logger;

class Rotator
{
    private array $proxies;

    private ?Logger $logger;

    public function __construct(Request $request, ?Logger $logger = null)
    {
        $this->logger = $logger;
        $addresses = Proxies::getAddresses($request);
        $this->proxies = Proxies::generateProxies($addresses);
        $this->log('New proxy list requested');
    }

    public function popProxy(): ?Proxy
    {
        $proxy = $this->proxies ? array_pop($this->proxies) : null;
        $this->log(sprintf('Proxy list has decreased, there are %d left', count($this->proxies)));

        return $proxy;
    }

    public function getProxies(): array
    {
        return $this->proxies;
    }

    private function log(string $message)
    {
        if ($this->logger) {
            $this->logger->info($message);
        }
    }
}