<?php
namespace Pylesos;

use Monolog\Logger;

class Puppeteer implements MotorInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function download(string $url, Rotator $rotator, array $postParams, Logger $logger): Response
    {
        $puppeteer = new \Nesk\Puphpeteer\Puppeteer();
        $proxy = $rotator->popProxy();
        $browser = $puppeteer->launch([
            'ignoreHTTPSErrors' => true,
            'args' => ['--proxy-server=' . $proxy->getAddress()]
        ]);
        $page = $browser->newPage();
        $page->authenticate([
            'username' => $proxy->getLogin(),
            'password' => $proxy->getPassword()
        ]);
        $page->goto($url);
        $content = $page->content();
        $browser->close();
        $response = new Response(
            $content,
            0,
            '',
            $proxy,
            $this->request
        );
        if ($this->request->isDebug()) {
            $response->setDebug([
                'options' => $this->request->getParams(),
                'puppeteer_response' => $response
            ]);
        }

        return $response;
    }
}