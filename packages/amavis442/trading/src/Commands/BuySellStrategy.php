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
use Amavis442\Trading\Models\Position;

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

    protected function updateBuyOrderStatusAndClosePosition()
    {
        $orders = Order::where(function ($q) {
            $q->whereIn('status', ['open', 'pending', 'manual']);
            $q->orWhereNull('status');
        })->whereSide('buy')->get();

        if ($orders->count()) {
            foreach ($orders as $order) {
                /** @var \GDAX\Types\Response\Authenticated\Order $exchangeOrder */
                $exchangeOrder = $this->exchange->getOrder($order->order_id);
                $order = $this->orderService->updateStatus($order, $exchangeOrder);

                if ($order->status == 'done') {
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
            }
        }
    }

    protected function updateSellOrdersAndOpenPosition()
    {
        $orders = \Amavis442\Trading\Models\Order::where(function ($q) {
            $q->whereIn('status', ['open', 'pending', 'manual']);
            $q->orWhereNull('status');
        })->whereSide('sell')->get();

        if ($orders->count()) {
            foreach ($orders as $order) {
                /** @var \GDAX\Types\Response\Authenticated\Order $exchangeOrder */
                $exchangeOrder = $this->exchange->getOrder($order->order_id);
                $order = $this->orderService->updateStatus($order, $exchangeOrder);

                if ($order->status == 'done') {
                    $position = $this->positionService->open(
                        $exchangeOrder->getProductId(),
                        $exchangeOrder->getId(),
                        $exchangeOrder->getSize(),
                        $exchangeOrder->getPrice()
                    );
                    $order->position_id = $position->id;
                    $order->save();

                    event(new PositionEvent(
                            $exchangeOrder->getProductId(),
                            $exchangeOrder->getSide(),
                            $exchangeOrder->getSize(),
                            $exchangeOrder->getPrice(),
                            $position->status
                        )
                    );
                }
            }
        }
    }

    protected function placeOrder(string $pair, string $cryptocoin, Collection $result, Position $position = null)
    {
        if (!$this->option('simulate', false)) {

            if (!is_null($position) && $result->get('side') == 'buy') {
                $position_id = $position->id;
            } else {
                $position_id = 0;
            }

            $this->info(
                'Placing order for crypto: ' . $cryptocoin . ',side: ' .
                $result->get('side') . ',size: ' . $result->get('size') .
                ' at price: ' . $result->get('price') .
                ' position_id: ' . $position_id
            );

            $placedOrder = $this->exchange->placeOrder(
                $pair,
                $result->get('side'),
                $result->get('size'),
                $result->get('price')
            );

            if ($placedOrder->getId() != null) {
                $position_id = 0;
                if (!is_null($position) && $result->get('side') == 'buy') {
                    $position_id = $position->id;
                }
                $this->orderService->insert($placedOrder, 'GrowingAndHarvesting', $position_id);
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

    protected function makePosition(
        string $pair,
        string $cryptocoin,
        $strategy,
        Collection $config,
        Position $position = null
    ) {
        $result = $strategy->advise($config, $position);
        if ($result->get('result') == 'fail' || $result->get('result') == 'hold') {
            Log::debug("No advise data available");
            return;
        }

        $placeOrder = false;
        if ($cryptocoin == 'BTC' && $result->get('size') >= 0.0001) {
            $placeOrder = true;
        } else {
            if ($result->get('size') >= 0.01) {
                $placeOrder = true;
            }
        }

        if ($placeOrder) {
            $this->placeOrder($pair, $cryptocoin, $result, $position);
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


        while (1) {
            $noExceptions = true;

            $this->updateSellOrdersAndOpenPosition();
            $this->updateBuyOrderStatusAndClosePosition();

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

            try {
                $funds = $this->exchange->getAccounts();
            } catch (\Exception $e) {
                Log::alert($e->getTraceAsString());
                $noExceptions = false;
            }

            $currentprice = 0;

            if ($noExceptions) {
                foreach ($funds as $fund) {
                    $available = $fund->getAvailable();

                    if ($fund->getCurrency() == $fundAccount) {
                        $config->put('fund', (float)number_format($available, 8, '.', ''));
                    }
                    if ($fund->getCurrency() == $cryptocoin) {
                        $config->put('coin', (float)number_format($available, 8, '.', ''));
                    }
                }

                $currentprice = $this->exchange->getCurrentPrice();

                $config->put('currentprice', $currentprice);
            }


            $funds = true;
            if ($config->get('fund', 0.0) == 0.00 && $config->get('coin', 0.0) == 0.0) {
                $this->warn("no funds for coin: {$cryptocoin} and fund: {$fundAccount}");
                Log::warning("no funds for coin: {$cryptocoin} and fund: {$fundAccount}");
                $funds = false;
            }

            if ($noExceptions) {
                if ($slots <= 0 || !$funds) {
                    Log::info('slots full (' . $settings->max_orders . '/' . $used_slots . ')');
                } else {

                    $openPositions = $this->positionService->getOpen($pair);

                    if ($openPositions->count() > 0) {
                        foreach ($openPositions as $openPosition) {
                            $this->makePosition($pair, $cryptocoin, $strategy, $config, $openPosition);
                        }
                    } else {
                        $this->makePosition($pair, $cryptocoin, $strategy, $config);
                    }
                }
            }
            sleep(2);
        }
    }
}
