<?php

namespace KafkaBus;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Repository as CacheContract;

class KafkaBusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('kafka-bus.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'kafka-bus');

        $this->app->bind(Consumer::class, function ($app) {
            return new Consumer($app->make(CacheContract::class));
        });
    }
}
