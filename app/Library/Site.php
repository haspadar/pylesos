<?php
namespace App\Library;

use App\Library\Proxy\Adapters\FreeProxySites;

class Site
{
    private $row;

    private Domain $domain;

    private array $options;

    public function __construct(string $url, array $options = [])
    {
        $this->domain = new Domain($url);
        $this->row = $this->getRow();
        if (!$this->row) {
            $scheme = parse_url($url)['scheme'] ?? 'http';
            \DB::insert('INSERT INTO sites (url, domain) values (:url, :domain)', [
                'url' => $scheme . '://' . $this->domain,
                'domain' => $this->domain
            ]);
            $this->row = $this->getRow();
        }

        $this->options = $options ?: $_ENV;
    }

    public function getId(): int
    {
        return $this->row->id;
    }

    public function getProxiesAdapters(): array
    {
        $adaptersNames = explode(',', $this->getOption('PROXY_ADAPTERS'));
        $adapters = [];
        foreach ($adaptersNames as $adapterName) {
            $className = 'App\Library\Proxy\Adapters\\' . $adapterName;
            $adapters[] = new $className();
        }

        return $adapters;
    }

    public function getDomain(): Domain
    {
        return $this->domain;
    }

    private function getRow()
    {
        $found = \DB::select('SELECT * FROM sites WHERE domain = :domain', [
            'domain' => $this->domain
        ]);

        return $found ? $found[0] : $found;
    }

    private function getOption(string $name): string
    {
        return  $this->options['SITES'][strtr($this->domain, ['.' => '_'])][$name] ?? '';
    }
}
