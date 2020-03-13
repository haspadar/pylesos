<?php

namespace App\Providers;

use App\Library\Services\FreeProxyCz;
use App\Library\Services\WhatIsMyBrowserCom;
use Illuminate\Support\ServiceProvider;

class SiteWithUserAgentsServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Library\Services\SiteWithParseUserAgents', function ($app) {
            return new WhatIsMyBrowserCom();
        });
    }
}
