<?php

namespace App\Console\Commands;

use App\Library\Services\SiteWithProxies;
use Carbon\Carbon;
use GuzzleHttp\Client;
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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param SiteWithProxies $site
     * @return mixed
     */
    public function handle(SiteWithProxies $site)
    {
        $proxies = $site->downloadProxies();
        $addedCount = 0;
        $updatedCount = 0;
        foreach ($proxies as $proxy) {
            if (app('db')
                ->table('proxies')
                ->where('address', $proxy->getAddress())
                ->update([
                    'address' => $proxy->getAddress(),
                    'protocol' => $proxy->getProtocol(),
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
                        'created_at' => Carbon::now('Europe/Minsk')->toDateTimeString()
                    ]);
                $addedCount++;
            }
        }

        $this->info(sprintf('Added %d new proxies, updated %d proxies', $addedCount, $updatedCount));

        return 0;
    }
}
