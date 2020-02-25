<?php
namespace App\Library;

use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;

class ProxyRotator
{
    private Site $site;

    private ?Proxy $liveProxy;

    public function __construct(Site $site)
    {
        $this->site = $site;
        if (!$this->hasSiteProxies()) {
            $this->addSiteProxies();
        }

        $this->setLiveProxy($this->getFirstSiteProxy());
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

    public function blockProxy(): void
    {
        if ($this->getLiveProxy()) {
            \DB::delete('DELETE FROM sites_proxies WHERE site_id = :site_id AND proxy_id = :proxy_id', [
                'site_id' => $this->site->getId(),
                'proxy_id' => $this->getLiveProxy()->getId()
            ]);
            if (!$this->hasSiteProxies()) {
                $this->addSiteProxies();
            }

            $this->setLiveProxy($this->getFirstSiteProxy());
        }
    }

    public function getFirstSiteProxy(): ?Proxy
    {
        $foundAll = \DB::select('SELECT * FROM sites_proxies WHERE site_id = ?', [$this->site->getId()]);
        if ($foundAll) {
            $proxy = \DB::select('SELECT * FROM proxies WHERE id = ?', [$foundAll[0]->proxy_id]);

            return new Proxy($proxy[0]->address, $proxy[0]->protocol);
        }

        return null;
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

    private function setLiveProxy(?Proxy $proxy): void
    {
        $this->liveProxy = $proxy;
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
}
