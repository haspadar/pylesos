<?php
namespace App\Library;

class UserAgentRotator
{
    use Rotator;

    public function getUserAgent(): string
    {
        return $this->getRow() ?? '';
    }
}
