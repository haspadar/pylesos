<?php

namespace App\Providers;

use App\Library\Services\FreeProxyCz;
use App\Library\Services\WhatIsMyBrowserCom;
use Illuminate\Support\ServiceProvider;

class DownloadUserAgentsProvider extends ServiceProvider
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
        $this->app->bind('App\Library\Services\SiteWithUserAgents', function ($app) {
            return new WhatIsMyBrowserCom();
        });
    }
}
