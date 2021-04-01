<?php
use Dotenv\Dotenv;
use Pylesos\PylesosService;
use Pylesos\Scheduler;
use simplehtmldom\HtmlDocument;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$env = $dotenv->load();
$scheduler = new Scheduler($env);
$scheduler->run(function () use ($env) {
    $response = PylesosService::download('https://losangeles.craigslist.org/', $env);
    $doc = new HtmlDocument($response->getResponse());
    foreach ($doc->find('h3.ban') as $level1) {
        $title = strip_tags($level1->find('a span', 0)->innertext);
        $url = $level1->find('a', 0)->href;
        var_dump([
            'title' => $title,
            'url' => $url
        ]);
        DB::insert('categories', [
            'title' => $title,
            'url' => $url
        ]);
    }
});