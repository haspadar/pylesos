<?php
namespace Pylesos;

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;

class Chrome implements MotorInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->checkSquidInstalled();
    }

    public function download(string $url, Rotator $rotator): Response
    {
        $capabilities = [WebDriverCapabilityType::BROWSER_NAME => 'chrome'];
//        $proxy = $this->getProxyFromSquid($squidRotator);
        $proxy = $rotator->popProxy();
//        if (Squid::isSquidProxy($proxy, $this->request)) {
            $squidConfigProxy =
//            $request->removeProxyAddress();
//            $rotator = new Rotator($request);
//            $proxy = $rotator->popProxy();
//
//            $generatedSquidConfig = $this->generateSquidConfig($squidProxy);
//            $this->reloadSquidConfig($generatedSquidConfig);
            $capabilities[WebDriverCapabilityType::PROXY] = [
                'proxyType' => 'manual',
                'httpProxy' => $proxy->getAddress(),
                'sslProxy' => $proxy->getAddress(),
            ];
//        }

        $desiredCapabilities = new DesiredCapabilities($capabilities);
        $options = new ChromeOptions();
//        $options->setBinary("/usr/bin/google-chrome");
        $options->addArguments(["--headless","--disable-gpu", "--no-sandbox"]);
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        $driver = RemoteWebDriver::create('http://localhost:9515', $desiredCapabilities);
        $driver->get($url);
        $chromeResponse = $driver->findElement(WebDriverBy::cssSelector('html'))->getAttribute('innerHTML');
        $response = new Response(
            $chromeResponse,
            0,
            '',
            $squidProxy = $rotator->popProxy(),
            $this->request
        );
        if ($this->request->isDebug()) {
            $response->setDebug([
                'options' => $this->request->getParams(),
                'chrome_capabilities' => $capabilities,
                'status' => $driver->getStatus(),
                'chrome_response' => $chromeResponse,
                'squid_config' => ''
            ]);
        }

        return $response;
    }

    public function checkInstall(): void
    {
        if (!file_exists(self::getDriver())) {
            throw new \Exception(
                'Драйвер не нейден: '
                    . self::getDriver()
                    . PHP_EOL
                    . 'Ссылка для скачивания: https://chromedriver.chromium.org/downloads'
            );
        }
    }

    private function getProxyPlugin(): string
    {
        $pluginForProxyLogin = '/tmp/a'.uniqid().'.zip';
        $zip = new \ZipArchive();
        $zip->open($pluginForProxyLogin, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFile(dirname(__DIR__) . '/sources/Selenium-Chrome-HTTP-Private-Proxy-master/manifest.json', 'manifest.json');
        $background = file_get_contents(dirname(__DIR__) . '/sources/Selenium-Chrome-HTTP-Private-Proxy-master/background.js');
        $background = str_replace(['%proxy_host', '%proxy_port', '%username', '%password'], ['83.217.11.172', '52812', '3iksnWNjeN', 'romenald'], $background);
        $zip->addFromString('background.js', $background);
        $zip->close();

        putenv("webdriver.chrome.driver=" . dirname(__DIR__) . '/sources/' . $this->request->getChromeDriver());
        $options = new ChromeOptions();
        $options->addArguments(['args' => ['--disable-extensions-except=/tmp/']]);
        $options->addExtensions([$pluginForProxyLogin]);
        $desiredCapabilities = DesiredCapabilities::chrome();
//        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $options);

//        $driver = ChromeDriver::start($desiredCapabilities);


        $desiredCapabilities->setCapability( ChromeOptions::CAPABILITY, $options );

        $driver = RemoteWebDriver::create('http://localhost:9515', $desiredCapabilities);


        $driver->get('https://api.ipify.org?format=json');
//
        file_put_contents('ip.png', $driver->takeScreenshot());
//        unlink($pluginForProxyLogin);
    }

    private static function getDriver(): string
    {
        return dirname(__DIR__) . '/sources/chromedriver';

//        Фикс ошибки X11 при запуске webdriver: sudo apt-get install gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget
//        Установка Chrome binary: wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo apt-key add -
//sudo sh -c 'echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google-chrome.list'
//sudo apt-get update
//sudo apt install google-chrome-stable


//        статья про то, что в headless прокси с паролем указать нельзя, и как настроить Squid: https://superuser.com/questions/1438057/accessing-internet-behind-proxy-in-headless-ubuntu-where-proxy-requires-login-th
//        решение https://stackoverflow.com/questions/48427498/how-to-use-proxy-with-authentication-in-headless-chorme-browser-using-selenium-a
//        несколько портов для squid: https://stackoverflow.com/questions/45966359/squid-listen-on-multiple-ports-and-forward-to-different-proxy

//        apt-get install xvfb

    }

    private function reloadSquidConfig(string $config)
    {
        var_dump($config);exit;
        file_put_contents('/etc/squid/squid.conf', $config);
        echo `sudo squid -f /etc/squid/squid.conf` . PHP_EOL;
    }

    private function checkSquidInstalled()
    {
        if (!$this->isSquidInstalled()) {
            throw new \Exception('Squid is not installed. Run "sudo apt-get install squid"');
        }
    }

    private function isSquidInstalled(): bool
    {
        $response = `squid -version`;
        $lines = explode(PHP_EOL, $response);

        return isset($lines[1]) && $lines[1] == 'Service Name: squid';
    }

    private function generateSquidConfig(Proxy $proxy): string
    {
        $configPattern = file_get_contents(dirname(__DIR__) . '/sources/squid.conf');

        return sprintf($configPattern, $proxy->getIp(), $proxy->getPort(), $proxy->getAuth());
    }

    private function getProxyFromSquid(Rotator $squidRotator)
    {
//        $this->request->getRotatorUrl()
//        $squidRotator->getList();


        $squidProxy = $squidRotator->popProxy();
        if ($squidProxy) {
//            $request->removeProxyAddress();
//            $rotator = new Rotator($request);
//            $proxy = $rotator->popProxy();
//
//            $generatedSquidConfig = $this->generateSquidConfig($squidProxy);
//            $this->reloadSquidConfig($generatedSquidConfig);
            $capabilities[WebDriverCapabilityType::PROXY] = [
                'proxyType' => 'manual',
                'httpProxy' => $squidProxy->getAddress(),
                'sslProxy' => $squidProxy->getAddress(),
            ];
        }

        return null;
    }
}