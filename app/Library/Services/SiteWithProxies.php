<?php
namespace App\Library\Services;

use App\Library\Services\Contracts\ProxiesSourceInterface;

abstract class SiteWithProxies implements ProxiesSourceInterface
{
    use SiteParserTrait;
}
