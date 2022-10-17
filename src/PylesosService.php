<?php
namespace Pylesos;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class PylesosService
{
    private static ?Rotator $rotator = null;

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

    public static function post(string $url, array $postParams, array $headers, array $env, int $count = 20): Response
    {
        return self::download($url, $postParams, $headers, $env, $count);
    }

    public static function get(string $url, array $headers, array $env, int $count = 20): Response
    {
        return self::download($url, [], $headers, $env, $count);
    }

    public static function download(string $url, array $postParams, array $headers, array $env, int $count): Response
    {
        $env['URL'] = $url;
        $request = new Request($env);
        $logger = new Logger('pylesos');
        if (php_sapi_name() === 'cli') {
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $error = $request->validate();
        if (!$error) {
            $motor = $request->generateMotor();
            if ($motor) {
                $rotator = self::getRotator($request, $logger);
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

    private static function getRotator(Request $request, Logger $logger): Rotator
    {
        if (!self::$rotator || !self::$rotator->getProxies()) {
            self::$rotator = new Rotator($request, $logger);
        }

        return self::$rotator;
    }
}