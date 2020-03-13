<?php

namespace App\Providers;

use App\Library\Services\EnvProxiesSitesList;
use App\Library\Services\FreeProxyCz;
use Illuminate\Support\ServiceProvider;

class SiteWithProxiesServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Library\Services\SiteWithParseProxies', function ($app) {
            return new FreeProxyCz();
        });
    }
}
