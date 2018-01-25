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
use Amavis442\Trading\Models\Ticker;

/**
 * Description of RunBotCommand
 *
 * @author patrick
 */
class Bot extends Command
{
    use GrandBot;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:run:bot {pair : what pair to use valid values are BTC-EUR, LTC-EUR, ETH-EUR } {--simulate : simulate for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buys and sells position depending on the given strategy.';

    /**
     * @var book
     */
    public $book;


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


    protected function process()
    {

        $pair = $this->config->get('pair');
        $cryptoCoin = $this->config->get('cryptoCoin');
        $fundAccount = $this->config->get('fundAccount');
        $strategy = $this->config->get('strategy');

        $noExceptions = true;

        $settings = Setting::wherePair($pair)->firstOrFail();

        $this->updateOrdersAndPositions(
            \Amavis442\Trading\Contracts\Order::ORDER_SELL,
            \Amavis442\Trading\Contracts\Position::POSITION_CLOSE
        );

        $this->updateOrdersAndPositions(
            \Amavis442\Trading\Contracts\Order::ORDER_BUY,
            \Amavis442\Trading\Contracts\Position::POSITION_OPEN
        );

        $currentPrice = Cache::get('gdax::' . $pair . '::currentprice', null);

        if (!is_null($currentPrice)) {
            $this->info('Orders and positions updated.');
            $this->info('Currentprice ' . $currentPrice);

            Cache::put('bot::settings', $settings->toJson(), 1);
            Cache::put('bot::pair', $pair, 1);

            Cache::put('bot::sellstradle', config('trading.sellstradle', 0.03), 1);
            Cache::put('bot::buystradle', config('trading.buystradle', 0.03), 1);

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
        } else {
            Log::critical('No currentprice');
        }
    }

    public function handle()
    {
        $strategy = $this->getStrategy();

        $this->config->put('strategy', $strategy);

        $pair = $this->argument('pair');
        if (!in_array($pair, ['BTC-EUR', 'LTC-EUR', 'ETH-EUR'])) {
            throw new \Exception('No valid pair given');
        }
        $this->config->put('pair', $pair);


        list($cryptoCoin, $fundAccount) = explode('-', $pair);

        $this->config->put('cryptoCoin', $cryptoCoin);
        $this->config->put('fundAccount', $fundAccount);

        $this->exchange->usePair($pair);

        if ($this->option('simulate')) {
            $this->config->put('simulate', true);
        }

        Cache::put('bot::process::timestamp', \Carbon\Carbon::now('Europe/Amsterdam'),10);

        $loop = \React\EventLoop\Factory::create();
        $connector = new \Ratchet\Client\Connector($loop);

        Cache::put('bot::heartbeat', date('Y-m-d H:i:s'), 1);

        $connector('wss://ws-feed.gdax.com')
            ->then(function (\Ratchet\Client\WebSocket $conn) use ($pair) {
                $channel['type'] = 'subscribe';
                $channel['product_ids'] = ['BTC-EUR', 'ETH-EUR', 'LTC-EUR'];
                $channel['channels'] = ['ticker'];

                $ch = json_encode($channel);
                $conn->send($ch);

                $conn->on('message',
                    function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($pair, $conn) {
                        $data = json_decode($msg, 1);

                        if ($data['type'] == 'ticker') {
                            Cache::put('gdax::' . $data['product_id'] . '::currentprice', $data['price'], 1);

                            Ticker::create([
                                'sequence'   => $data['sequence'],
                                'pair'       => $data['product_id'],
                                'timeid'     => \Carbon\Carbon::now('Europe/Amsterdam')->format('YmdHis'),
                                'price'      => $data['price'],
                                'open'       => $data['open_24h'],
                                'high'       => $data['high_24h'],
                                'low'        => $data['low_24h'],
                                'close'      => $data['price'],
                                'volume'     => $data['volume_24h'],
                                'volume_30d' => $data['volume_30d'],
                                'best_bid'   => $data['best_bid'],
                                'best_ask'   => $data['best_ask'],
                            ]);

                            try {
                                /** @var \Carbon\Carbon $processTimeStamp */
                                $this->process();
                            } catch (\Exception $e) {
                                Log::critical('Bot::process made an error: ' . $e->getMessage());
                                Log::critical($e->getTraceAsString());
                            }
                        }

                        Cache::put('bot::heartbeat', date('Y-m-d H:i:s'), 1);
                    });


                $conn->on('close', function ($code = null, $reason = null) use ($pair) {
                    /** log errors here */
                    Log::warning("Connection closed ({$code} - {$reason})");
                    Cache::forget('bot::heartbeat');
                    Cache::forget('gdax::' . $pair . '::currentprice');
                });

            },
                function (\Exception $e) use ($loop, $pair) {
                    Log::critical("Could not connect: " . $e->getMessage());
                    Log::critical($e->getTraceAsString());
                    Cache::forget('bot::heartbeat');
                    Cache::forget('gdax::' . $pair . '::currentprice');

                    $loop->stop();
                }
            );

        $loop->run();

    }
}
