<?php

namespace Amavis442\Trading\Commands;

use Amavis442\Trading\Bot\Traits\GrandBot;
use Amavis442\Trading\Services\OrderService;
use Amavis442\Trading\Services\PositionService;
use Amavis442\Trading\Strategies\GrowingAndHarvesting;
use Illuminate\Console\Command;
use Amavis442\Trading\Contracts\Exchange;
use Amavis442\Trading\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Description of RunBotCommand
 *
 * @author patrick
 */
class BuySellStrategy extends Command
{
    use GrandBot;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:run:buysellstrategy {pair : what pair to use valid values are BTC-EUR, LTC-EUR, ETH-EUR } {--simulate : simulate for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buys and sells position depending on the given strategy.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Exchange $exchange, OrderService $orderService, PositionService $positionService)
    {
        $this->exchange = $exchange;
        $this->orderService = $orderService;
        $this->positionService = $positionService;

        $this->config = new Collection();

        parent::__construct();
    }


    public function getStrategy()
    {
        return new GrowingAndHarvesting();
    }

    public function handle()
    {
        $strategy = $this->getStrategy();

        $pair = $this->argument('pair');
        if (!in_array($pair, ['BTC-EUR', 'LTC-EUR', 'ETH-EUR'])) {
            throw new \Exception('No valid pair given');
        }

        list($cryptoCoin, $fundAccount) = explode('-', $pair);

        $this->exchange->usePair($pair);

        if ($this->option('simulate')) {
            $this->config->put('simulate', true);
        }

        while (1) {
            $noExceptions = true;

            $settings = Setting::first();

            $this->updateOrdersAndPositions(
                \Amavis442\Trading\Contracts\Order::ORDER_SELL,
                \Amavis442\Trading\Contracts\Position::POSITION_OPEN
            );

            $this->updateOrdersAndPositions(
                \Amavis442\Trading\Contracts\Order::ORDER_BUY,
                \Amavis442\Trading\Contracts\Position::POSITION_CLOSE
            );

            Cache::put('bot::settings', $settings->toJson(), 1);
            Cache::put('bot::pair', $pair, 1);
            Cache::put('bot::stradle', 0.03, 1);


            if ($settings->botactive) {
                $openOrders = $this->orderService->getNumOpenOrders($pair);
                try {
                    $openExchangeOrders = $this->exchange->getOpenOrders();
                } catch (\Exception $e) {
                    Log::alert($e->getTraceAsString());
                    $noExceptions = false;
                }

                if ($openExchangeOrders) {
                    $used_slots = count($openExchangeOrders);
                } else {
                    $used_slots = $openOrders;
                }
                $slots = $settings->max_orders - $used_slots;

                try {
                    $funds = $this->exchange->getAccounts();
                } catch (\Exception $e) {
                    Log::alert($e->getTraceAsString());
                    $noExceptions = false;
                }

                if ($noExceptions) {
                    foreach ($funds as $fund) {
                        $available = $fund->getAvailable();
                        if ($fund->getCurrency() == $fundAccount) {
                            Cache::put('config::fund', (float)number_format($available, 8, '.', ''), 1);
                        }
                        if ($fund->getCurrency() == $cryptoCoin) {
                            Cache::put('config::coin', (float)number_format($available, 8, '.', ''), 1);
                        }
                    }
                    $currentprice = $this->exchange->getCurrentPrice();

                    Cache::put('gdax::' . $pair . '::currentprice', $currentprice, 2);
                }

                $funds = true;
                if (Cache::get('config::fund', 0.0) == 0.00 && Cache::get('config::coin', 0.0) == 0.0) {
                    $this->warn("no funds for coin: {$cryptoCoin} and fund: {$fundAccount}");
                    Log::warning("no funds for coin: {$cryptoCoin} and fund: {$fundAccount}");
                    $funds = false;
                }

                if ($noExceptions) {
                    if ($slots <= 0 || !$funds) {
                        Log::info('slots full (' . $settings->max_orders . '/' . $used_slots . ')');
                    } else {
                        $openPositions = $this->positionService->getOpen($pair);
                        if ($openPositions->count() > 0) {
                            foreach ($openPositions as $openPosition) {
                                $this->strategyAdvise($strategy, $openPosition);
                            }
                        } else {
                            $this->strategyAdvise($strategy);
                        }
                    }
                }
            } else {
                Log::info('Bot not active');
            }
            sleep(5);
        }
    }
}
