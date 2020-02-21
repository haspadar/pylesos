<?php
namespace App\Library;

class UserAgentRotator
{
    private Site $site;

    private string $liveUserAgent;

    public function __construct(string $url)
    {
        $this->site = new Site($url);
        if (!$this->hasSiteUsersAgents()) {
            $this->addSiteUsersAgents();
        }

        $this->liveUserAgent = $this->getFirstSiteUserAgent();
    }

    public function getLiveUserAgent(): string
    {
        return $this->liveUserAgent;
    }

    public function getUsersAgentsCount(): int
    {
        return \DB::select('SELECT COUNT(*) AS count FROM sites_users_agents WHERE site_id = ?', [
            $this->site->getId()
        ])[0]->count;
    }

    public function blockUserAgent(string $userAgent): bool
    {
        $userAgentId = \DB::table('users_agents')->where('user_agent', $userAgent)->get()[0]->id;
        $isBlocked = \DB::table('sites_users_agents')
            ->where('site_id', $this->site->getId())
            ->where('user_agent_id', $userAgentId)
            ->delete();
        if (!$this->hasSiteUsersAgents()) {
            $this->addSiteUsersAgents();
        }

        $this->liveUserAgent = $this->getFirstSiteUserAgent();

        return $isBlocked;
    }

    private function getSite($url): Site
    {
        return $this->site;
    }

    private function hasSiteUsersAgents(): bool
    {
        return \DB::select('SELECT * FROM sites_users_agents WHERE site_id = :site_id', [
            'site_id' => $this->site->getId()
        ]) ? true : false;
    }

    private function addSiteUsersAgents(): void
    {
        foreach (\DB::select('SELECT * FROM users_agents') as $userAgent) {
            \DB::insert('INSERT INTO sites_users_agents (site_id, user_agent_id) values (?, ?)', [
                $this->site->getId(),
                $userAgent->id
            ]);
        }
    }

    private function getFirstSiteUserAgent(): string
    {
        $foundAll = \DB::select('SELECT * FROM sites_users_agents WHERE site_id = ?', [$this->site->getId()]);
        if ($foundAll) {
            $userAgents = \DB::select('SELECT * FROM users_agents WHERE id = ?', [$foundAll[0]->user_agent_id]);

            return $userAgents[0]->user_agent;
        }

        return '';
    }
}
