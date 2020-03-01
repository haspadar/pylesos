<?php

namespace App\Console\Commands;

use App\Library\Domain;
use App\Library\Services\ProxiesSitesList;
use App\Library\Services\SiteWithProxies;
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
        /**
         * @var $site SiteWithProxies
         */
        foreach ($sitesList->getSites() as $site) {
            $domain = new Domain($site->getDomain());
            try {
                $this->info(sprintf('Site %s parsing started', $domain));
                $proxies = $site->downloadProxies();
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

        return 0;
    }
}
