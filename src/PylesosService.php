<?php
namespace Pylesos;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class PylesosService
{
    public static function download(string $url, array $env, int $count = 20): Response
    {
        $env['URL'] = $url;
        $request = new Request($env);
        $logger = new Logger('pylesos');
        $logger->pushHandler(new StreamHandler($env['LOG_PATH']));
        $error = $request->validate();
        if (!$error) {
            $motor = $request->generateMotor();
            if ($motor) {
                $rotator = new Rotator($request);
                $pylesos = new Pylesos($motor, $rotator, $logger);
                $attemptNumber = 1;
                do {
                    $logger->debug('Download ' . $request->getUrl() . ', attempt #' . $attemptNumber++);
                    $response = $pylesos->download($request->getUrl());
                } while ($response->isBan($logger) && --$count > 0);

                return $response;
            } else {
                $logger->error('Motor not found');

                throw new Exception('Motor not found');
            }

        } else {
            $logger->error($error);

            throw new Exception($error);
        }
    }
}