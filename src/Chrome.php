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
    }

    public function download(string $url, Rotator $rotator): Response
    {
        $capabilities = [WebDriverCapabilityType::BROWSER_NAME => 'chrome'];
        $proxy = $rotator->popProxy();
        $capabilities[WebDriverCapabilityType::PROXY] = [
            'proxyType' => 'manual',
            'httpProxy' => $proxy->getAddress(),
            'sslProxy' => $proxy->getAddress(),
        ];
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
            $proxy,
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

    /**
     * Подгружает плагин для ввода логина и пароля в X11
     * Может работать только с виртульаный рабочим столом xvfb, но его на homestead не запустил
     *
     * @return string
     */
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
        $desiredCapabilities->setCapability( ChromeOptions::CAPABILITY, $options );

        $driver = RemoteWebDriver::create('http://localhost:9515', $desiredCapabilities);
        $driver->get('https://api.ipify.org?format=json');

        file_put_contents('ip.png', $driver->takeScreenshot());
        unlink($pluginForProxyLogin);
    }
}