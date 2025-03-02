<?php
namespace Pylesos;

use Monolog\Logger;

class Pylesos
{
    private MotorInterface $motor;
    /**
     * @var Rotator
     */
    private Rotator $rotator;

    private ?Logger $logger;

    public function __construct(MotorInterface $motor, Rotator $rotator, ?Logger $logger = null)
    {
        $this->motor = $motor;
        $this->rotator = $rotator;
        $this->logger = $logger;
    }

    public function download(string $url, $postParams = [], array $headers = []): Response
    {
        return $this->motor->download($url, $this->rotator, $postParams, $headers, $this->logger);
    }
}