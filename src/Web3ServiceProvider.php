<?php

namespace Aoeng\Laravel\Web3;


use Illuminate\Support\ServiceProvider;

class Web3ServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/web3.php' => config_path('web3.php'),
        ], 'web3');

    }

    public function register()
    {
        $this->app->singleton('web3', function ($app) {
            return new Web3($app);
        });

        $this->app->singleton('eth', function ($app) {
            return new Eth($app);
        });

        $this->app->singleton('eth-contract', function ($app) {
            return new Contract($app);
        });
    }

}
