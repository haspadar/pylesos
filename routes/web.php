<?php

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
    $site = new \App\Library\Site($page);
    $proxyRotator = new \App\Library\ProxyRotator(
        \App\Library\ProxyRotator::findLiveProxies($site->getId())
    );
    $userAgentRotator = new \App\Library\UserAgentRotator(
        \App\Library\UserAgentRotator::findLiveUsersAgents($site->getId())
    );
    $pylesos = new \App\Library\Pylesos(
        new \App\Library\Motor(),
        $proxyRotator,
        $userAgentRotator,
        false
    );
    try {
        $response = $pylesos->download($page);
    } catch (\Exception $e) {}
dd($pylesos->getReport()->getBadProxies());
    return response()->json([
        'content' => $response ?? '',
        'report' => $pylesos->getReport()->getBadProxies()
    ]);
});
