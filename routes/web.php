<?php

use App\Console\Commands\DownloadProxies;
use App\Console\Commands\DownloadUserAgents;
use App\Library\Motor;
use App\Library\ProxyRotator;
use App\Library\Pylesos;
use App\Library\Report;
use App\Library\Site;
use App\Library\UserAgentRotator;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->get('/download', function (Request $request) use ($router) {
    $this->validate($request, [
        'page' => 'required|url'
    ]);
    $page = $_GET['page'];
    $site = new Site($page);
    $proxyRotator = new ProxyRotator(DownloadProxies::findLiveProxies($site));
    $userAgentRotator = new UserAgentRotator(DownloadUserAgents::findLiveUsersAgents($site));
    $pylesos = new Pylesos(
        new Motor(),
        $proxyRotator,
        $userAgentRotator,
        new Report($page)
    );
    $response = $pylesos->download($page, true);

    return response()->json([
        'content' => $response ?? '',
        'report' => $pylesos->getReport()
    ]);
});
