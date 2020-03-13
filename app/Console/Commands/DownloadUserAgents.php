<?php

namespace App\Console\Commands;

use App\Library\Services\SiteWithParseUserAgents;
use App\Library\Site;
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
     * @param SiteWithParseUserAgents $site
     * @return mixed
     */
    public function handle(SiteWithParseUserAgents $site)
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

    public static function findLiveUsersAgents(Site $site): array
    {
        $rows = \DB::select('SELECT * FROM users_agents WHERE user_agent NOT IN(SELECT user_agent FROM connections WHERE domain = :domain AND is_skipped = 1)', [
            'domain' => $site->getDomain()
        ]);
        $usersAgents = [];
        foreach ($rows as $row) {
            $usersAgents[] = $row->user_agent;
        }

        return $usersAgents;
    }
}
