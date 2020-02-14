<?php
namespace App\Library\Services;

use App\Library\Services\Contracts\UserAgentsSourceInterface;

abstract class SiteWithUserAgents implements UserAgentsSourceInterface
{
    use SiteParserTrait;
}
