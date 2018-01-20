<?php

namespace Amavis442\Trading;

use Illuminate\Support\ServiceProvider;

use Amavis442\Trading\Commands\Ticker;
use Amavis442\Trading\Commands\Position;
use Amavis442\Trading\Commands\Order;



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
                Ticker::class,
                Position::class,
                Order::class,
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
            'Amavis442\Trading\Contracts\Exchange', 'Amavis442\Trading\Exchanges\GDaxExchange'
        );

        $this->app->singleton('Amavis442\Trading\Services\OrderService', function ($app) {
            return new \Amavis442\Trading\Services\OrderService($app->make('Amavis442\Trading\Contracts\Exchange'));
        });

        $this->app->singleton('Amavis442\Trading\Services\PositionService', function ($app) {
            return new \Amavis442\Trading\Services\PositionService();
        });


        $this->app->singleton('Amavis442\Trading\Strategies\Stoploss', function ($app) {
            return new \Amavis442\Trading\Strategies\Stoploss();
        });


        // The bots
        $this->app->singleton('Amavis442\Trading\Bot\PositionBot', function ($app) {
            $bot = new \Amavis442\Trading\Bot\PositionBot($app->make('Amavis442\Trading\Contracts\Exchange'));
            $bot->setStopLossService($app->make('Amavis442\Trading\Triggers\Stoploss'));
            return $bot;
        });

        $this->app->singleton('Amavis442\Trading\Bot\OrderBot', function ($app) {
            return new \Amavis442\Trading\Bot\OrderBot($app->make('Amavis442\Trading\Contracts\Exchange'));
        });

        $this->app->singleton('Amavis442\Trading\Bot\TickerBot', function ($app) {
            return new \Amavis442\Trading\Bot\TickerBot($app->make('Amavis442\Trading\Contracts\Exchang'));
        });

        /* WIP
        $this->app->singleton('Amavis442\Trading\Bot\BuyBot', function ($app) {
            return new \Amavis442\Trading\Bot\BuyBot($app->make('Amavis442\Trading\Contracts\Exchange'));
        });
        */
    }

}
