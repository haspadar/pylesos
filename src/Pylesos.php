<?php
namespace Pylesos;

class Pylesos
{
    private MotorInterface $motor;

    public function __construct(MotorInterface $motor)
    {
        $this->motor = $motor;
    }

    public function download(string $url, Rotator $rotator, Squid $squid): Response
    {
        return $this->motor->download($url, $rotator, $squid);
    }
}