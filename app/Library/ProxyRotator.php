<?php
namespace App\Library;

class ProxyRotator
{
    use Rotator;

    public function getProxy(): ?Proxy
    {
        return $this->getRow() ?? null;
    }

    /**
     * @param int $siteId
     * @return Proxy[]
     */
    public static function findLiveProxies(int $siteId): array
    {
        $rows = \DB::select('SELECT * FROM proxies WHERE address NOT IN(SELECT proxy FROM responses WHERE site_id = :site_id AND is_banned = 1) ORDER BY created_at DESC', [
            'site_id' => $siteId
        ]);
        $proxies = [new Proxy()];
        foreach ($rows as $row) {
            $proxies[] = new Proxy($row->address, $row->protocol);
        }

        return $proxies;
    }
}
