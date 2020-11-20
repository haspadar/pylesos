<?php
namespace Pylesos;

class Pylesos
{
    private MotorInterface $motor;
    /**
     * @var Rotator
     */
    private Rotator $rotator;

    public function __construct(MotorInterface $motor, Rotator $rotator)
    {
        $this->motor = $motor;
        $this->rotator = $rotator;
    }

    public function download(string $url): Response
    {
        return $this->motor->download($url, $this->rotator);
    }
}