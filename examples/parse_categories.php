<?php
use Dotenv\Dotenv;
use Pylesos\PylesosService;
use Pylesos\Scheduler;
use simplehtmldom\HtmlDocument;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$env = $dotenv->load();
$scheduler = new Scheduler($env);
$db = new MeekroDB('localhost', $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_NAME']);
$scheduler->run(function () use ($env, $db) {
    $response = PylesosService::download('https://losangeles.craigslist.org/', $env);
    $doc = new HtmlDocument($response->getResponse());
    foreach ($doc->find('h3.ban') as $level1) {
        $title = strip_tags($level1->find('a span', 0)->innertext);
        $url = $level1->find('a', 0)->href;
        $db->insertIgnore('categories', [
            'title' => $title,
            'donor_url' => $url,
            'url' => '',
            'create_time' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
        $level1Id = $db->insertId();
        var_dump([
            'id' => $level1Id,
            'title' => $title,
            'url' => $url
        ]);
    }
});