<?php
namespace Pylesos;

class PylesosService
{
    public static function download(string $url, array $env, int $count = 20): Response
    {
        $env['URL'] = $url;
        $request = new Request($env);
        $error = $request->validate();
        if (!$error) {
            $motor = $request->generateMotor();
            if ($motor) {
                $rotator = new Rotator($request);
                $pylesos = new Pylesos($motor, $rotator);
                do {
                    $response = $pylesos->download($request->getUrl());
                } while ($response->isBan() && --$count > 0);

                return $response;
            } else {
                throw new Exception('Motor not found');
            }

        } else {
            throw new Exception($error);
        }
    }
}