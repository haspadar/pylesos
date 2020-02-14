<?php
namespace App\Library;

class Site
{
    private $row;

    /**
     * @var Domain
     */
    private Domain $domain;

    public function __construct(string $url)
    {
        $this->domain = new Domain($url);
        $this->row = $this->getRow();
        if (!$this->row) {
            $scheme = parse_url($url)['scheme'] ?? 'http';
            \DB::insert('INSERT INTO sites (url, domain) values (:url, :domain)', [
                'url' => $scheme . '://' . $this->domain,
                'domain' => $this->domain
            ]);
            $this->row = $this->getRow();
        }
    }

    public function getId(): int
    {
        return $this->row->id;
    }

    public function getDomain(): Domain
    {
        return $this->domain;
    }

    private function getRow()
    {
        $found = \DB::select('SELECT * FROM sites WHERE domain = :domain', [
            'domain' => $this->domain
        ]);

        return $found ? $found[0] : $found;
    }
}
