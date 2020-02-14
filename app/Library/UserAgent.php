<?php
namespace App\Library;

class UserAgent
{
    /**
     * @var string
     */
    private string $userAgent;
    /**
     * @var bool
     */
    private bool $isMobile;

    public function __construct(string $userAgent, bool $isMobile)
    {
        $this->userAgent = $userAgent;
        $this->isMobile = $isMobile;
    }

    public function isMobile(): bool
    {
        return $this->isMobile;
    }

    public function __toString()
    {
        return $this->userAgent;
    }
}
