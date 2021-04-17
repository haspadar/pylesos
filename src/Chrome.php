<?php
namespace Pylesos;

use HeadlessChromium\BrowserFactory;
use League\CLImate\TerminalObject\Basic\Tab;

class Chrome implements MotorInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function download(string $url, Rotator $rotator): Response
    {
        $proxy = $rotator->popProxy();
        $path = $this->request->getParam(Request::CHROME_PATH);
        $browserFactory = new BrowserFactory($path);
        $options = array_filter([
//          'customFlags' => [
//              '--proxy-server="http://localhost:8080"'
//          ],
            'userAgent' => $this->request->getUserAgent($url)
        ]);
        $browser = $browserFactory->createBrowser($options);
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
                $proxy,
                $this->request
            );
            if ($this->request->isDebug()) {
                $response->setDebug([
                    'options' => $this->request->getParams(),
                    'browser_options' => $options,
                    'path' => $path,
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
}