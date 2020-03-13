<?php

namespace App\Console\Commands;

use App\Library\Domain;
use App\Library\Proxy;
use App\Library\Services\ProxiesSitesList;
use App\Library\Services\SiteWithParseProxies;
use App\Library\Site;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DownloadProxies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxies:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download proxies from site';

    public function handle(ProxiesSitesList $sitesList): int
    {
        $sites = $sitesList->getSites();
        if ($sites) {
            /**
             * @var $siteWithProxies SiteWithParseProxies
             */
            foreach ($sitesList->getSites() as $siteWithProxies) {
                $domain = new Domain($siteWithProxies->getDomain());
                try {
                    $this->info(sprintf('Site %s parsing started', $domain));
                    $proxies = $siteWithProxies->downloadProxies();
                    $addedCount = 0;
                    $updatedCount = 0;
                    foreach ($proxies as $proxy) {
                        if (app('db')
                            ->table('proxies')
                            ->where('address', $proxy->getAddress())
                            ->update([
                                'updated_at' => Carbon::now('Europe/Minsk')->toDateTimeString()
                            ])
                        ) {
                            $updatedCount++;
                        } else {
                            app('db')
                                ->table('proxies')
                                ->insert([
                                    'address' => $proxy->getAddress(),
                                    'protocol' => $proxy->getProtocol(),
                                    'domain' => $domain,
                                    'adapter' => $siteWithProxies->getProxyAdapter(),
                                    'created_at' => Carbon::now('Europe/Minsk')->toDateTimeString()
                                ]);
                            $addedCount++;
                        }
                    }

                    $this->info(sprintf(
                        'Added %d new proxies, updated %d proxies from %s',
                        $addedCount,
                        $updatedCount,
                        $domain
                    ));
                } catch (\Exception $e) {
                    $this->error(sprintf('Site %s ignored: %s' . PHP_EOL, $domain, $e->getMessage()));
                }
            }
        } else {
            $this->warn('Setting PROXIES_SITES is empty in .env');
        }

        return 0;
    }

    /**
     * @param Site $site
     * @return Proxy[]
     */
    public static function findLiveProxies(Site $site): array
    {
        $rows = \DB::select('SELECT * FROM proxies WHERE address NOT IN(SELECT proxy FROM connections WHERE domain = :domain AND is_skipped = 1)', [
            'domain' => $site->getDomain()
        ]);
        $proxies = [new Proxy()];
        foreach ($rows as $row) {
            $proxies[] = new Proxy($row->address, $row->protocol);
        }

        return $proxies;
    }
}
