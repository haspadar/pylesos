<?php
namespace Pylesos;

use HeadlessChromium\Browser\ProcessAwareBrowser;
use HeadlessChromium\BrowserFactory;
use League\CLImate\TerminalObject\Basic\Tab;
use Monolog\Logger;

class Chrome implements MotorInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function createBrowser(?Proxy $proxy = null): ProcessAwareBrowser
    {
        $path = $this->request->getParam(Request::CHROME_PATH);
        $browserFactory = new BrowserFactory($path);

        return $browserFactory->createBrowser($this->getOptions($proxy));
    }

    public function download(string $url, Rotator $rotator, $postParams, array $headers, Logger $logger): Response
    {
        $proxy = $rotator->popProxy();
        $browser = $this->createBrowser($proxy);
        try {
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation();
            $pageTitle = $page->evaluate('document.title')->getReturnValue();
            $userAgent = $page->evaluate('window.navigator.userAgent')->getReturnValue();
            $chromeResponse = $page->getHtml();
            $response = new Response(
                $chromeResponse,
                0,
                '',
                '',
                $proxy,
                $this->request
            );
            if ($this->request->isDebug()) {
                $response->setDebug([
                    'options' => $this->request->getParams(),
                    'browser_options' => $this->getOptions($url, $proxy),
                    'path' => $this->request->getParam(Request::CHROME_PATH),
                    'chrome_response' => $chromeResponse,
                    'squid_config' => '',
                    'page_title' => $pageTitle,
                    'user_agent' => $userAgent
                ]);
            }

            return $response;

        } finally {
            $browser->close();
        }
    }

    private function getOptions(?Proxy $proxy = null): array
    {
        return array_filter([
//          'customFlags' => [
//              '--proxy-server="http://localhost:8080"'
//          ],
            'userAgent' => ''
        ]);
    }
}