<?php

namespace Cels\Aegis;

use Cels\Aegis\Http\Client as AegisClient;
use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Monolog\Logger;

class AegisServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../stubs/config/aegis.php' => config_path('aegis.php'),
        ], 'aegis-config');

        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'aegis');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../stubs/config/aegis.php', 'aegis');

        $this->app->singleton('aegis', function ($app) {
            $credentials = $app['config']['aegis']['project'];

            return new Aegis(
                new AegisClient($credentials['slug'], $credentials['token'])
            );
        });

        if ($logManager = $this->app->make(LogManager::class)) {
            $logManager->extend('aegis', function ($app, $config) {
                $handler = new MonologHandler($this->app->make('aegis'));

                return new Logger('aegis', [$handler]);
            });
        }
    }
}
