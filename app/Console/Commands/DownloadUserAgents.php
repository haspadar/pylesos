<?php

namespace App\Console\Commands;

use App\Library\Services\SiteWithUserAgents;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DownloadUserAgents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users_agents:download';

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
        $userAgents = $site->downloadUserAgents();
        $addedCount = 0;
        $updatedCount = 0;
        foreach ($userAgents as $userAgent) {
            if (app('db')
                ->table('users_agents')
                ->where('user_agent', $userAgent)
                ->update([
                    'user_agent' => $userAgent,
                    'updated_at' => Carbon::now('Europe/Minsk')->toDateTimeString()
                ])
            ) {
                $updatedCount++;
            } else {
                app('db')
                    ->table('users_agents')
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
