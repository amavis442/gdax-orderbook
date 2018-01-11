<?php

namespace Amavis442\Trading;

use Amavis442\Trading\Commands\RunTicker;
use Illuminate\Support\ServiceProvider;


/**
 * Class TraderServiceProvider
 *
 * @see     https://laravel.com/docs/5.5/packages
 *
 * @package Amavis442\Trading
 */
class TraderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
                             __DIR__ . '/config/config.php' => config_path('trading.php'),

                         ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/migrations/');

        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        /*

         $this->loadViewsFrom(__DIR__.'/resources/views', 'courier');

        $this->publishes([
                             __DIR__.'/path/to/assets' => public_path('vendor/courier'),
                         ], 'public');
        */

        if ($this->app->runningInConsole()) {
            $this->commands([
                                RunTicker::class,

                            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind(
            'Amavis442\Trading\Contracts\GdaxServiceInterface',
            'Amavis442\Trading\Services\GDaxService'
        );

        $this->app->bind(
            'Amavis442\Trading\Contracts\OrderServiceInterface',
            'Amavis442\Trading\Services\OrderService'
        );

        $this->app->bind(
            'Amavis442\Trading\Contracts\PositionServiceInterface',
            'Amavis442\Trading\Services\PositionService'
        );

        /*$this->app->singleton('HelpSpot\API', function ($app) {
            return new HelpSpot\API($app->make('HttpClient'));
        }); */
    }
}
