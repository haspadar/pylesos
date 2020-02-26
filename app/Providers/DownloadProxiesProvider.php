<?php

namespace App\Providers;

use App\Library\Services\FreeProxyCz;
use App\Library\Services\GetProxyListCom;
use App\Library\Services\ProxyListDownload;
use Illuminate\Support\ServiceProvider;

class DownloadProxiesProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Library\Services\SiteWithProxies', function ($app) {
            return new FreeProxyCz();
        });
    }
}
