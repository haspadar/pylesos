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
        $proxies = $site->downloadProxies(new Client([
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2
//                CURLOPT_PROXY => 'proxyip:58080'
            ],
            'timeout' => 5,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148']
        ]));
        foreach ($proxies as $proxy) {
            if (!app('db')
                ->table('proxies')
                ->where('address', $proxy->getAddress())
                ->update([
                    'address' => $proxy->getAddress(),
                    'protocol' => $proxy->getProtocol(),
                    'updated_at' => Carbon::now('Europe/Minsk')->toDateTimeString()
                ])
            ) {
                app('db')
                    ->table('proxies')
                    ->insert([
                        'address' => $proxy->getAddress(),
                        'protocol' => $proxy->getProtocol(),
                        'created_at' => Carbon::now('Europe/Minsk')->toDateTimeString()
                    ]);
            }
        }

        return 0;
    }
}
