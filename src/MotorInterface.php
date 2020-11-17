<?php
namespace Pylesos;

interface MotorInterface
{
    public function __construct(Request $request);

    public function download(string $url, Rotator $rotator): Response;
}
