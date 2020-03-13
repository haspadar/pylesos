<?php

namespace App\Library;

use App\Library\Report\Connection;
use App\Library\Report\Page;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;

class Report
{
    private int $id;

    private Domain $domain;

    private string $page;

    private array $connections;

    public function __construct(string $page)
    {
        $this->page = $page;
        $this->id = \DB::table('reports')->insertGetId([
            'domain' => new Domain($page),
            'created_at' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function add(
        Proxy $proxy,
        string $userAgent,
        string $responseContent,
        ?ResponseInterface $response,
        ?\Exception $exception = null
    ): void {
        $this->connections[] = new Connection(
            $proxy,
            $userAgent,
            $responseContent,
            $response,
            $exception
        );
    }

    /**
     * @return Connection[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }
}
