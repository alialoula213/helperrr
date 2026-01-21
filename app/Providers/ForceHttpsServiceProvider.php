<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class ForceHttpsServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (config('app.env') === 'production' && config('cyber_miner.force_https', 'disabled') === 'enabled') {
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    public function boot(UrlGenerator $url)
    {
        if (config('app.env') === 'production' && config('cyber_miner.force_https', 'disabled') === 'enabled') {
            $url->forceScheme('https');
        }
    }
}