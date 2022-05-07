<?php

namespace Cels\Aegis;

use Cels\Aegis\Http\Client as AegisClient;
use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Monolog\Logger;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        if (\function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../config/aegis.php' => config_path('aegis.php'),
            ]);
        }

        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'aegis');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aegis.php', 'aegis');

        $this->app->singleton('aegis', function ($app) {
            return new Aegis(
                new AegisClient(
                    config('aegis.project.slug'),
                    config('aegis.project.token'),
                )
            );
        });

        if ($logManager = $this->app->make('log')) {
            if (!($logManager instanceof LogManager)) {
                return;
            }

            $logManager->extend('aegis', function ($app, $config) {
                $handler = new Handler($this->app->make('aegis'));

                return new Logger('aegis', [$handler]);
            });
        }
    }
}
