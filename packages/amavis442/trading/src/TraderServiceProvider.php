<?php

namespace Amavis442\Trading;

use Illuminate\Support\ServiceProvider;

use Amavis442\Trading\Commands\RunTicker;
use Amavis442\Trading\Commands\UpdatePositions;



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

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');

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
                UpdatePositions::class,
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
            'Amavis442\Trading\Contracts\GdaxServiceInterface', 'Amavis442\Trading\Services\GDaxService'
        );

        $this->app->bind(
            'Amavis442\Trading\Contracts\OrderServiceInterface', 'Amavis442\Trading\Services\OrderService'
        );

        $this->app->bind(
            'Amavis442\Trading\Contracts\PositionServiceInterface', 'Amavis442\Trading\Services\PositionService'
        );

        $this->app->singleton('Amavis442\Trading\Bot\PositionBot', function ($app) {
            return new \Amavis442\Trading\Bot\PositionBot($app->make('Amavis442\Trading\Contracts\GdaxServiceInterface'));
        });

        $this->app->singleton('Amavis442\Trading\Bot\OrderBot', function ($app) {
            return new \Amavis442\Trading\Bot\OrderBot($app->make('Amavis442\Trading\Contracts\GdaxServiceInterface'));
        });


        $this->app->singleton('Amavis442\Trading\Contracts\IndicatorManagerInterface', function ($app) {
            $manager = new \Amavis442\Trading\Managers\IndicatorManager();
            $manager->add('mfi', new \Amavis442\Trading\Indicators\MoneyFlowIndexIndicator());
            return $manager;
          });
    }

}
