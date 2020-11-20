<?php
namespace Pylesos;

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class Firefox implements MotorInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $host = $this->request->getWebDriverHost();
        $desiredCapabilities = DesiredCapabilities::firefox();
        $desiredCapabilities->setCapability('moz:firefoxOptions', ['args' => ['-headless'], 'binary' => dirname(__DIR__) . '/sources/firefox/firefox-bin']);



        $driver = RemoteWebDriver::create($host, $desiredCapabilities);
        $driver->get('https://api.ipify.org?format=json');
//
        file_put_contents('ip.png', $driver->takeScreenshot());
//        $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
//        $this->checkInstall();
    }

    public function download(string $url, Rotator $rotator): Response
    {
    }



    private static function getDriver(): string
    {

//        https://github.com/mozilla/geckodriver/releases
//sudo apt-get install firefox
    }
}