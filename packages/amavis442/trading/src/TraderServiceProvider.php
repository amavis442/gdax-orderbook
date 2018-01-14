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
            'Amavis442\Trading\Contracts\ExchangeInterface', 'Amavis442\Trading\Exchanges\GDaxExchange'
        );

        $this->app->bind(
            'Amavis442\Trading\Contracts\OrderServiceInterface', 'Amavis442\Trading\Services\OrderService'
        );

        $this->app->bind(
            'Amavis442\Trading\Contracts\PositionServiceInterface', 'Amavis442\Trading\Services\PositionService'
        );



        $this->app->singleton('Amavis442\Trading\Trigger\Stoploss', function ($app) {
            return new \Amavis442\Trading\Triggers\Stoploss();
        });


        // The bots
        $this->app->singleton('Amavis442\Trading\Bot\PositionBot', function ($app) {
            $bot = new \Amavis442\Trading\Bot\PositionBot($app->make('Amavis442\Trading\Contracts\ExchangeInterface'));
            $bot->setStopLossService($app->make('Amavis442\Trading\Triggers\Stoploss'));
            return $bot;
        });

        $this->app->singleton('Amavis442\Trading\Bot\OrderBot', function ($app) {
            return new \Amavis442\Trading\Bot\OrderBot($app->make('Amavis442\Trading\Contracts\ExchangeInterface'));
        });

        $this->app->singleton('Amavis442\Trading\Bot\TickerBot', function ($app) {
            return new \Amavis442\Trading\Bot\TickerBot($app->make('Amavis442\Trading\Contracts\ExchangeInterface'));
        });

        /* WIP
        $this->app->singleton('Amavis442\Trading\Bot\BuyBot', function ($app) {
            return new \Amavis442\Trading\Bot\BuyBot($app->make('Amavis442\Trading\Contracts\ExchangeInterface'));
        });
        */


        $this->app->singleton('Amavis442\Trading\Contracts\IndicatorManagerInterface', function ($app) {
            $manager = new \Amavis442\Trading\Managers\IndicatorManager();
            $manager->add('mfi', new \Amavis442\Trading\Indicators\MoneyFlowIndexIndicator());
            return $manager;
          });
    }

}
