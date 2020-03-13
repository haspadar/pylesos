<?php
namespace App\Library\Services;

use App\Library\Services\Contracts\ParseProxiesSourceInterface;

abstract class SiteWithParseProxies implements ParseProxiesSourceInterface
{
    use SiteParserTrait;

    public function getProxyAdapter(): string
    {
        $domainParts = explode('.', explode('//', $this->getDomain())[1]);

        if (count($domainParts) > 1) {
            return $this->replacePart($domainParts[count($domainParts) - 2])
                . $this->replacePart($domainParts[count($domainParts) - 1]);
        }

        return $this->replacePart($domainParts[0]);
    }

    private function replacePart(string $part): string
    {
        $part = ucfirst(strtr($part, [
            '-' => '_',
            '.' => '_'
        ]));

        return implode('',
            array_map(
                function (string $part) {
                    return ucfirst($part);
                },
                explode('_', $part)
            )
        );
    }
}
