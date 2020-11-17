<?php

namespace Pylesos;

use League\CLImate\CLImate;

class Request
{
    public const MOTOR_CURL = 'curl';

    public const MOTOR_CHROME = 'chrome';

    public const MOTOR_FIREFOX = 'firefox';

    private const URL = 'url';

    private const PROXY_ADDRESS = 'address';

    private const PROXY_AUTH = 'auth';

    private const MOTOR = 'motor';

    private const DEBUG = 'debug';

    private const ROTATOR_URL = 'rotator_url';

    private const ROTATOR_PROXIES = 'rotator_proxies';

    private const BAN_WORDS = 'ban_words';

    private const BAN_CODES = 'ban_codes';

    private const MOBILE_USER_AGENT = 'mobile_user_agent';

    private const DESKTOP_USER_AGENT = 'desktop_user_agent';

    private const WEB_DRIVER_HOST = 'web_driver_host';

    private const CHROME_DRIVER = 'chrome_driver';

    private array $cliParams;

    private string $error = '';

    private array $envParams;

    private array $params;

    private const CLI_NAMES = [
        self::URL,
        self::PROXY_ADDRESS,
        self::PROXY_AUTH,
        self::ROTATOR_URL,
        self::MOTOR,
        self::BAN_WORDS,
        self::BAN_CODES,
        self::DEBUG,
        self::MOBILE_USER_AGENT,
        self::DESKTOP_USER_AGENT,
        self::WEB_DRIVER_HOST,
        self::CHROME_DRIVER,
    ];

    private const DEFAULTS = [
        self::MOTOR => self::MOTOR_CURL,
        self::DEBUG => 0
    ];

    public function __construct(array $env)
    {
        $this->cliParams = $this->filterCliParams();
        $this->envParams = $this->filterEnvParams($env);
        $this->params = array_replace(self::DEFAULTS, $this->envParams, $this->cliParams);
    }

    public function validate(): string
    {
        $this->validateUrl()
            && $this->validateProxyAddress()
            && $this->validateProxyAuth()
            && $this->validateRotatorUrl()
            && $this->validateRotatorProxies()
            && $this->validateMotor();

        return $this->error;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getRotatorUrl(): string
    {
        return $this->getParam(self::ROTATOR_URL);
    }

    public function getBanCodes(): array
    {
        return $this->getArrayParam(self::BAN_CODES);
    }

    public function getBanWords(): array
    {
        return $this->getArrayParam(self::BAN_WORDS);
    }

    public function getRotatorProxies(): array
    {
        return $this->getArrayParam(self::ROTATOR_PROXIES);
    }

    public function getWebDriverHost(): string
    {
        return $this->getParam(self::WEB_DRIVER_HOST);
    }

    public function getProxyAddress(): string
    {
        return $this->getParam(self::PROXY_ADDRESS);
    }

    public function getProxyAuth(): string
    {
        return $this->getParam(self::PROXY_AUTH);
    }

    public function getMotor(): string
    {
        return $this->getParam(self::MOTOR);
    }

    public function getUrl(): string
    {
        return $this->getParam(self::URL);
    }

    public function generateMotor(): MotorInterface
    {
        try {
            if ($this->getMotor() == Request::MOTOR_CURL) {
                return new Curl($this);
            }

            if ($this->getMotor() == Request::MOTOR_CHROME) {
                return new Chrome($this);
            }
        } catch (\Exception $e) {
            $climate = new CLImate();
            $climate->error($e->getMessage());
            exit;
        }
    }

    public function getParam(string $name): string
    {
        return $this->getParams()[trim($name)] ?? '';
    }

    public function getArrayParam(string $name): array
    {
        return $this->parseArrayParam(trim($this->getParam($name)));
    }

    public function isDebug(): bool
    {
        return $this->getParam(self::DEBUG);
    }

    public function canConvertToArray(string $value): bool
    {
        return mb_strpos($value, PHP_EOL) !== false;
    }

    public function getUserAgent(string $url): string
    {
        $isMobileUrl = substr($url, 0, 2) == 'm.';

        return $isMobileUrl
            ? $this->getMobileUserAgent()
            : $this->getDesktopUserAgent();
    }

    public function getMobileUserAgent(): string
    {
        return $this->getParam(self::MOBILE_USER_AGENT);
    }

    public function getDesktopUserAgent(): string
    {
        return $this->getParam(self::DESKTOP_USER_AGENT);
    }

    public function parseArrayParam(string $value): array
    {
        $params = [];
        foreach (explode(PHP_EOL, $value) as $arrayValue) {
            $filteredValue = trim($arrayValue);
            if ($filteredValue) {
                $params[] = $filteredValue;
            }
        }

        return $params;
    }

    private function validateUrl(): bool
    {
        if ($this->params[self::URL]) {
            $parsed = parse_url($this->params[self::URL]);
            $scheme = $parsed['scheme'] ?? 'https';
            $host = $parsed['host'] ?? '';
            $path = $parsed['path'] ?? '';
            $query = $parsed['query'] ?? '';
            $fullUrl = $scheme . '://' . $host . $path . ($query ? $query : '');
            if (!filter_var($fullUrl, FILTER_VALIDATE_URL)) {
                $this->error = 'Невалидный URL';
            }
        } else {
            $this->error = 'Укажите URL';
        }

        return $this->error ? false : true;
    }

    private function validateProxyAddress(): bool
    {
        if (isset($this->params[self::PROXY_ADDRESS])) {
            $parts = explode(':', $this->params[self::PROXY_ADDRESS]);
            $ip = $parts[0] ?? '';
            $port = $parts[1] ?? '';
            if ($ip && !filter_var($ip, FILTER_VALIDATE_IP)) {
                $this->error = 'Невалидный IP прокси';
            } elseif ($port && !is_numeric($port)) {
                $this->error = 'Невалидный порт прокси';
            }
        }

        return $this->error ? false : true;
    }

    private function validateProxyAuth(): bool
    {
        if (isset($this->params[self::PROXY_AUTH])) {
            $parts = explode(':', $this->params[self::PROXY_AUTH]);
            $login = $parts[0] ?? '';
            $password = $parts[1] ?? '';
            if ($login && !$password) {
                $this->error = 'Укажите пароль';
            }
        }

        return $this->error ? false : true;
    }

    private function validateRotatorProxies(): bool
    {
        if (isset($this->params[self::ROTATOR_PROXIES])
            && $this->params[self::ROTATOR_PROXIES]
        ) {

            $this->error = 'Невалидный url ротатора: ' . $this->params[self::ROTATOR_URL];
        }

        return $this->error ? false : true;
    }

    private function validateRotatorUrl(): bool
    {
        if (isset($this->params[self::ROTATOR_URL])
            && $this->params[self::ROTATOR_URL]
            && !filter_var($this->params[self::ROTATOR_URL], FILTER_VALIDATE_URL)
        ) {
            $this->error = 'Невалидный url ротатора: ' . $this->params[self::ROTATOR_URL];
        }

        return $this->error ? false : true;
    }

    private function validateMotor(): bool
    {
        if ($this->params[self::MOTOR] && !in_array($this->params[self::MOTOR], [
            self::MOTOR_CURL,
            self::MOTOR_CHROME,
            self::MOTOR_FIREFOX
        ])) {
            $this->error = 'Невалидный мотор: допускаются curl, chrome, firefox';
        }

        return $this->error ? false : true;
    }

    private function filterEnvParams(array $env): array
    {
        $envParams = [];
        foreach ($env as $name => $value) {
            $lowerName = strtolower($name);
            $filteredName = in_array($lowerName, self::CLI_NAMES) ? $lowerName : $name;
            $envParams[$filteredName] = $value;
        }

        return $envParams;
    }

    private function filterCliParams(): array
    {
        $cliNames = array_map(fn($name) => $name . ':', self::CLI_NAMES);

        return getopt('', $cliNames);
    }

    public function getChromeDriver(): string
    {
        return $this->getParam(self::CHROME_DRIVER);
    }
}