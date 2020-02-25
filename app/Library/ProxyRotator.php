<?php
namespace App\Library;

class ProxyRotator
{
    private Site $site;

    private ?Proxy $liveProxy;

    public function __construct(string $url)
    {
        $this->site = new Site($url);
        if (!$this->hasSiteProxies()) {
            $this->addSiteProxies();
        }

        $this->liveProxy = $this->getFirstSiteProxy();
    }

    public function getLiveProxy(): ?Proxy
    {
        return $this->liveProxy;
    }

    public function getProxiesCount(): int
    {
        return \DB::select('SELECT COUNT(*) AS count FROM sites_proxies WHERE site_id = ?', [
            $this->site->getId()
        ])[0]->count;
    }

    public function blockProxy(Proxy $proxy): bool
    {
        $isBlocked = \DB::delete('DELETE FROM sites_proxies WHERE site_id = :site_id AND proxy_id = :proxy_id', [
            'site_id' => $this->site->getId(),
            'proxy_id' => $proxy->getId()
        ]);
        if (!$this->hasSiteProxies()) {
            $this->addSiteProxies();
        }

        $this->liveProxy = $this->getFirstSiteProxy();

        return $isBlocked;
    }

    private function getSite($url): Site
    {
        return $this->site;
    }

    private function hasSiteProxies(): bool
    {
        return \DB::select('SELECT * FROM sites_proxies WHERE site_id = :site_id', [
            'site_id' => $this->site->getId()
        ]) ? true : false;
    }

    private function addSiteProxies(): void
    {
        foreach (\DB::select('SELECT * FROM proxies') as $proxyRow) {
            \DB::insert('INSERT INTO sites_proxies (site_id, proxy_id) values (?, ?)', [
                $this->site->getId(),
                $proxyRow->id
            ]);
        }
    }

    private function getFirstSiteProxy(): ?Proxy
    {
        $foundAll = \DB::select('SELECT * FROM sites_proxies WHERE site_id = ?', [$this->site->getId()]);
        if ($foundAll) {
            $proxy = \DB::select('SELECT * FROM proxies WHERE id = ?', [$foundAll[0]->proxy_id]);

            return new Proxy($proxy[0]->address, $proxy[0]->protocol);
        }

        return null;
    }
}
