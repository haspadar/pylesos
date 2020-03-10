<?php
namespace App\Library;

class UserAgentRotator
{
    use Rotator;

    public function getLiveUserAgent(): string
    {
        return $this->getRow() ?? '';
    }

    public static function findLiveUsersAgents(int $siteId): array
    {
        $rows = \DB::select('SELECT * FROM users_agents WHERE user_agent NOT IN(SELECT user_agent FROM responses WHERE site_id = :site_id AND is_banned = 1) ORDER BY created_at DESC', [
            'site_id' => $siteId
        ]);
        $usersAgents = [];
        foreach ($rows as $row) {
            $usersAgents[] = $row->user_agent;
        }

        return $usersAgents;
    }
}
