<?php
namespace App\Library\Services;

use App\Library\Services\Contracts\ParseUserAgentsSourceInterface;

abstract class SiteWithParseUserAgents implements ParseUserAgentsSourceInterface
{
    use SiteParserTrait;
}
