<?php
namespace Pylesos;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class PylesosService
{
    public static function getWithoutProxy(string $url, array $headers, array $env): Response
    {
        $env['ROTATOR_URL'] = '';
        $env['PROXY'] = '';
        $env['PROXIES'] = '';

        return self::get($url, $headers, $env, 1);
    }

    public static function postWithoutProxy(string $url, array $postParams, array $headers, array $env): Response
    {
        $env['ROTATOR_URL'] = '';
        $env['PROXY'] = '';
        $env['PROXIES'] = '';

        return self::post($url, $postParams, $headers, $env, 1);
    }

    public static function post(string $url, array $postParams, array $headers, array $env, int $count = 20, ?Rotator $rotator = null): Response
    {
        return self::download($url, $postParams, $headers, $env, $count, $rotator);
    }

    public static function get(string $url, array $headers, array $env, int $count = 20, ?Rotator $rotator = null): Response
    {
        return self::download($url, [], $headers, $env, $count, $rotator);
    }

    public static function createRotator(array $env): Rotator
    {
        $request = new Request($env);
        $error = $request->validate();
        if (!$error) {
            return new Rotator($request);
        } else {
            throw new Exception($error);
        }
    }

    public static function download(string $url, array $postParams, array $headers, array $env, int $count, ?Rotator $rotator = null): Response
    {
        $env['URL'] = $url;
        $request = new Request($env);
        $logger = new Logger('pylesos');
        $logger->pushHandler(new StreamHandler('php://stdout'));
        $error = $request->validate();
        if (!$error) {
            $motor = $request->generateMotor();
            if ($motor) {
                if (!$rotator) {
                    $rotator = new Rotator($request);
                }

                $pylesos = new Pylesos($motor, $rotator, $logger);
                $attemptNumber = 1;
                do {
                    $logger->info('Download ' . $request->getUrl() . ', attempt #' . $attemptNumber++);
                    $response = $pylesos->download($request->getUrl(), $postParams, $headers);
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