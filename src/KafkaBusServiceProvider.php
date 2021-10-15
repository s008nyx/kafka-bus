<?php

namespace KafkaBus;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Config\Repository as ConfigContract;

class KafkaBusServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'kafka-bus');

        $this->app->bind(Consumer::class, function ($app) {
            return new Consumer(
                $app->make(CacheContract::class),
                $app->make(ConfigContract::class)
            );
        });
    }
}
