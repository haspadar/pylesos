<?php

namespace App\Library\Report;

use App\Library\Proxy;
use App\Library\Report;
use Psr\Http\Message\ResponseInterface;

class Connection
{
    private string $userAgent;

    private string $responseContent;

    private ?ResponseInterface $response;

    private ?\Exception $exception;

    private Proxy $proxy;

    public function __construct(
        Proxy $proxy,
        string $userAgent,
        string $responseContent,
        ?ResponseInterface $response,
        ?\Exception $exception = null
    ) {
        $this->proxy = $proxy;
        $this->userAgent = $userAgent;
        $this->responseContent = $responseContent;
        $this->response = $response;
        $this->exception = $exception;
    }

    public function getException(): ?\Exception
    {
        return $this->exception;
    }
}
