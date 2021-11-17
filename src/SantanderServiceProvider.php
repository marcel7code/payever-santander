<?php

namespace Payever\Santander;

use Illuminate\Support\ServiceProvider;

class SantanderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // register routes
        $this->loadRoutesFrom(__DIR__ . '/routes/routes.php');

        // register config
        $this->mergeConfigFrom(__DIR__ . '/config/santander-payment.php', 'santander');

        // register vendor publish command
        $this->publishes([
            __DIR__ . '/config/santander-payment.php' => config_path('santander-payment.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/assets/' => public_path('vendor/payever'),
        ], 'public');
    }

    public function register()
    {
        // 
    }
}
