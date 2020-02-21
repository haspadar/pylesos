<?php

namespace App\Console\Commands;

use App\Library\Services\SiteWithProxies;
use App\Library\Services\SiteWithUserAgents;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class DownloadUserAgents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user_agents:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download user agents from site';

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
     * @param SiteWithUserAgents $site
     * @return mixed
     */
    public function handle(SiteWithUserAgents $site)
    {
        $userAgents = $site->downloadUserAgents(new Client([
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2
//                CURLOPT_PROXY => 'proxyip:58080'
            ],
            'timeout' => 5,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148']
        ]));
        $addedCount = 0;
        $updatedCount = 0;
        foreach ($userAgents as $userAgent) {
            if (app('db')
                ->table('user_agents')
                ->where('address', $userAgent)
                ->update([
                    'user_agent' => $userAgent,
                    'updated_at' => Carbon::now('Europe/Minsk')->toDateTimeString()
                ])
            ) {
                $updatedCount++;
            } else {
                app('db')
                    ->table('user_agents')
                    ->insert([
                        'user_agent' => $userAgent,
                        'is_mobile' => mb_strpos($userAgent, 'Mobile') !== false,
                        'created_at' => Carbon::now('Europe/Minsk')->toDateTimeString()
                    ]);
                $addedCount++;
            }
        }

        $this->info(sprintf('Added %d new User-Agents, updated %d User-Agents', $addedCount, $updatedCount));

        return 0;
    }
}
