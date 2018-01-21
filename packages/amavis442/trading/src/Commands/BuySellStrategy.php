<?php

namespace Amavis442\Trading\Commands;

use Amavis442\Trading\Services\OrderService;
use Amavis442\Trading\Services\PositionService;
use Amavis442\Trading\Strategies\GrowingAndHarvesting;
use Illuminate\Console\Command;
use Amavis442\Trading\Contracts\Exchange;
use Amavis442\Trading\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Amavis442\Trading\Events\Position as PositionEvent;
use Amavis442\Trading\Models\Order;

/**
 * Description of RunBotCommand
 *
 * @author patrick
 */
class BuySellStrategy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:run:buysellstrategy {cryptocoin=BTC : what currency to use } {fund=EUR : where the money has to go and come from } {--simulate : simulate for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buys and sells position depending on the given strategy.';

    protected $exchange;
    protected $orderService;
    protected $positionService;

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

        parent::__construct();
    }


    public function updateBuyOrderStatusAndClosePosition()
    {
        $orders = Order::where(function ($q) {
            $q->whereIn('status', ['open', 'pending', 'manual']);
            $q->orWhereNull('status');
        })->whereSide('buy')->get();

        if ($orders->count()) {
            foreach ($orders as $order) {
                /** @var \GDAX\Types\Response\Authenticated\Order $exchangeOrder */
                $exchangeOrder = $this->exchange->getOrder($order->order_id);
                $position_id = 0;
                $status = $exchangeOrder->getStatus();

                if ($status) {
                    // A recently placed buy order had been filled so we add it as an open position
                    if ($order->status == 'done') {
                        // Check if the sell order has a position and if so, close the position
                        $position_id = $order->position_id;
                        if ($position_id > 0) {
                            try {
                                $position = $this->positionService->close(
                                    $position_id,
                                    $exchangeOrder->getSize(),
                                    $exchangeOrder->getPrice()
                                );

                                event(new PositionEvent(
                                        $exchangeOrder->getProductId(),
                                        $exchangeOrder->getSide(),
                                        $exchangeOrder->getSize(),
                                        $exchangeOrder->getPrice(),
                                        $position->status
                                    )
                                );

                            } catch (\Exception $e) {
                                Log::error($e->getTraceAsString());
                            }
                        }
                    }

                    $order->status = $exchangeOrder->getStatus();
                } else {
                    $order->status = $exchangeOrder->getMessage();
                }
                $order->position_id = $position_id;
                $order->save();
            }
        }
    }

    public function updateSellOrdersAndOpenPosition()
    {
        $orders = \Amavis442\Trading\Models\Order::where(function ($q) {
            $q->whereIn('status', ['open', 'pending', 'manual']);
            $q->orWhereNull('status');
        })->whereSide('sell')->get();


        if ($orders->count()) {
            foreach ($orders as $order) {
                $exchangeOrder = $this->exchange->getOrder($order->order_id);
                $status = $exchangeOrder->getStatus();

                if ($status) {
                    // A recently placed buy order had been filled so we add it as an open position
                    if ($status == 'done') {
                        $position = $this->positionService->open(
                            $exchangeOrder->getProductId(),
                            $exchangeOrder->getId(),
                            $exchangeOrder->getSize(),
                            $exchangeOrder->getPrice()
                        );
                        $order->position_id = $position->id;

                        event(new PositionEvent(
                                $exchangeOrder->getProductId(),
                                $exchangeOrder->getSide(),
                                $exchangeOrder->getSize(),
                                $exchangeOrder->getPrice(),
                                $position->status
                            )
                        );
                    }
                    $order->status = $exchangeOrder->getStatus();
                } else {
                    $order->status = $exchangeOrder->getMessage();
                }
                $order->save();
            }
        }
    }

    protected function placeOrder(string $pair, string $cryptocoin, Collection $result)
    {
        if (!$this->option('simulate', false)) {
            $this->info(
                'Placing order for crypto: ' . $cryptocoin . ',side: ' .
                $result->get('side') . ', size: ' . $result->get('size') .
                ' at price: ' . $result->get('price')
            );

            $placedOrder = $this->exchange->placeOrder(
                $pair,
                $result->get('side'),
                $result->get('size'),
                $result->get('price')
            );

            if ($placedOrder->getId() != null) {
                $this->orderService->insert($placedOrder, 'GrowingAndHarvesting');
            } else {
                Log::info('Order not placed because: ' . $placedOrder->getMessage());
                $this->warn('Order not placed because: ' . $placedOrder->getMessage());
            }
        } else {
            $this->comment(
                'Simulate: Placing order for crypto: ' . $cryptocoin . ',side: ' .
                $result->get('side') . ', size: ' . $result->get('size') .
                ' at price: ' . $result->get('price')
            );
        }
    }

    public function handle()
    {
        $strategy = new GrowingAndHarvesting();

        $cryptocoin = $this->argument('cryptocoin');
        $fundAccount = $this->argument('fund');
        $pair = $cryptocoin . '-' . $fundAccount;

        $settings = (new Setting())->first();
        $config = new Collection();

        $this->exchange->useCoin($cryptocoin);


        while(1) {
            $this->updateSellOrdersAndOpenPosition();

            $openOrders = $this->orderService->getNumOpenOrders('BTC-EUR');
            $openExchangeOrders = $this->exchange->getOpenOrders();

            $used_slots = count($openExchangeOrders) + $openOrders;

            $slots = $settings->max_orders - $used_slots;


            $config->put('size', 0.0);
            $config->put('fund', 0.0);
            $config->put('coin', 0.0);
            $config->put('currentprice', 100000000);

            switch ($cryptocoin) {
                case 'BTC':
                    $config->put('size', 0.001);
                    break;
                case 'ETH':
                    $config->put('size', 0.01);
                    break;
                case 'LTC':
                    $config->put('size', 0.01);
                    break;
            }

            $funds = $this->exchange->getAccounts();
            foreach ($funds as $fund) {
                if ($fund->getCurrency() == $fundAccount) {
                    $config->put('fund', (float)number_format($fund->getAvailable(), 2, '.', ''));
                }
                if ($fund->getCurrency() == $cryptocoin) {
                    $config->put('coin', (float)number_format($fund->getAvailable(), 8, '.', ''));
                }
            }


            $config->put('currentprice', $this->exchange->getCurrentPrice());

            $funds = true;
            if ($config->get('fund', 0.0) == 0.00 && $config->get('coin', 0.0) == 0.0) {
                $this->warn("no funds for coin: {$cryptocoin} and fund: {$fundAccount}");
                $funds = false;
            }

            // Use all the account EUR to buy
            // sell 0.001 coin and buy it 30 lower unless currentprice

            // Check if slots are open and f not check if positions are still open (order canceld
            //

            if ($slots <= 0 || !$funds) {
                $this->warn('slots full (' . $settings->max_orders . '/' . $used_slots . ')');
            } else {

                $openPosition = $this->positionService->getOpen($pair);
                if ($openPosition) {
                    $position = $openPosition->first();
                } else {
                    $position = null;
                }
                $result = $strategy->advise($config, $position);
                $placeOrder = false;

                if ($cryptocoin == 'BTC' && $result->get('size') >= 0.0001) {
                    $placeOrder = true;
                } else {
                    if ($result->get('size') >= 0.01) {
                        $placeOrder = true;
                    }
                }


                // Make sure the buy price is under the sell price
                if (!is_null($position) && (float)$position->open < (float)$config->get('currentprice')) {
                    $placeOrder = false;
                    $this->warn(\Carbon\Carbon::now('Europe/Amsterdam')->format('Y-m-d H:i:s').' Currentprice is above sell price');
                }

                if ($placeOrder) {
                    $this->placeOrder($pair, $cryptocoin, $result);
                } else {
                    $this->info('Not placing order');
                }
            }

            $this->updateBuyOrderStatusAndClosePosition();

            sleep(5);
        }
    }
}
