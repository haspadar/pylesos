<?php
namespace Pylesos;

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class Chrome implements MotorInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
//        $host = $this->request->getWebDriverHost();
        var_dump($this->getProxyPlugin());exit;
//        $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
//        $this->checkInstall();
    }

    public function download(string $url, Rotator $rotator): Response
    {

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
        $pluginForProxyLogin = dirname(__DIR__) . '/sources/Selenium-Chrome-HTTP-Private-Proxy-master.zip';
        $zip = new \ZipArchive();
        $zip->open($pluginForProxyLogin, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFile(dirname(__DIR__) . '/sources/Selenium-Chrome-HTTP-Private-Proxy-master/manifest.json', 'manifest.json');
        $background = file_get_contents(dirname(__DIR__) . '/sources/Selenium-Chrome-HTTP-Private-Proxy-master/background.js');
        $background = str_replace(['%proxy_host', '%proxy_port', '%username', '%password'], ['83.217.11.172', '52812', '3iksnWNjeN', 'romenald'], $background);
        $zip->addFromString('background.js', $background);
        $zip->close();

        putenv("webdriver.chrome.driver=" . dirname(__DIR__) . '/sources/' . $this->request->getChromeDriver());


        $options = new ChromeOptions();
        $options->addExtensions([$pluginForProxyLogin]);
        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);

        $driver = ChromeDriver::start($caps);

        $driver->get('https://api.ipify.org?format=json');

        header('Content-Type: image/png');
        echo $driver->takeScreenshot();


        unlink($pluginForProxyLogin);
        exit;
    }

    private static function getDriver(): string
    {
        return dirname(__DIR__) . '/sources/chromedriver';

//        sudo apt-get install gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget
//        sudo apt install default-jre
//        sudo apt install openjdk-8-jre-headless
    }
}