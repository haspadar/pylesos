<?php
namespace App\Library;

class Domain
{
    private string $domain;

    public function __construct(string $url)
    {
        $this->domain = $this->extractDomain($url);
    }

    public function __toString(): string
    {
        return $this->domain;
    }

    private function extractDomain(string $url): string
    {
        if ($url && $parsed = parse_url($url)) {
            if (!isset($parsed['host'])) {
                $parsed = parse_url('http://' . $url);
            }

            return $this->removeWww($parsed['host']);
        }

        return '';
    }

    private function removeWww($domain): string
    {
        if (substr($domain, 0, 4) == 'www.') {
            $domain = substr($domain, 4);
        }

        return $domain;
    }
}
