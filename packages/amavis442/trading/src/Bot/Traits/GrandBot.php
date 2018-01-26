<?php

namespace Amavis442\Trading\Bot\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Amavis442\Trading\Contracts\Exchange;
use Amavis442\Trading\Events\PositionEvent;
use Amavis442\Trading\Models\Order;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Services\OrderService;
use Amavis442\Trading\Services\PositionService;
use Amavis442\Trading\Models\Setting;
use Amavis442\Trading\Contracts\Order as OrderContract;
use Amavis442\Trading\Contracts\Position as PositionContract;
use Amavis442\Trading\Contracts\Strategy as StrategyContract;

trait GrandBot
{
    /** @var Exchange */
    protected $exchange;
    /** @var OrderService */
    protected $orderService;
    /** @var PositionService */
    protected $positionService;
    /** @var Collection */
    protected $config;

    protected function updateOrdersAndPositions(
        $side = OrderContract::ORDER_BUY,
        $positionState = PositionContract::POSITION_OPEN
    ) {
        $orders = Order::where(
            function ($q) {
                $q->whereIn('status', ['open', 'pending', 'manual']);
                $q->orWhereNull('status');
            }
        )
                       ->whereSide($side)
                       ->get();

        if ($orders->count()) {
            foreach ($orders as $order) {
                try {
                    /** @var \GDAX\Types\Response\Authenticated\Order $exchangeOrder */
                    $exchangeOrder = $this->exchange->getOrder($order->order_id);
                } catch (\Exception $e) {
                    Log::warning($e->getTraceAsString());
                }

                $order = $this->orderService->updateStatus($order, $exchangeOrder);

                if ($order->status == 'done') {
                    $positionStatus = '';

                    if ($positionState == PositionContract::POSITION_OPEN) {
                        $position           = $this->positionService->open(
                            $exchangeOrder->getProductId(),
                            $exchangeOrder->getId(),
                            $exchangeOrder->getSize(),
                            $exchangeOrder->getPrice()
                        );
                        $positionStatus     = $position->status;
                        $order->position_id = $position->id;
                        $order->save();
                    }

                    if ($positionState == PositionContract::POSITION_CLOSE) {
                        $position_id = $order->position_id;
                        if ($position_id > 0) {
                            $position       = $this->positionService->close(
                                $position_id,
                                $exchangeOrder->getSize(),
                                $exchangeOrder->getPrice()
                            );
                            $positionStatus = $position->status;
                        }
                    }

                    if ($positionState == PositionContract::POSITION_OPEN ||
                        $positionState == PositionContract::POSITION_CLOSE
                    ) {
                        event(
                            new PositionEvent(
                                $exchangeOrder->getProductId(),
                                $exchangeOrder->getSide(),
                                $exchangeOrder->getSize(),
                                $exchangeOrder->getPrice(),
                                $positionStatus
                            )
                        );
                    }
                }
            }
        }
    }

    protected function placeOrder(Collection $strategyResult, Position $position = null)
    {
        $pair = Cache::get('bot::pair', null);

        $simulate = $this->config->get('simulate', false);
        if (!$simulate && !is_null($pair)) {
            $placedOrder = $this->exchange->placeOrder(
                $pair,
                $strategyResult->get('side'),
                $strategyResult->get('size'),
                $strategyResult->get('price')
            );

            if ($placedOrder->getId() != null) {
                $position_id = 0;
                if (!is_null($position)) {
                    $position_id = $position->id;
                }
                $this->orderService->insert($placedOrder, $strategyResult->get('strategy'), $position_id);
            } else {
                Log::info('Order not placed because: ' . $placedOrder->getMessage());
            }
        } else {
            Log::info('Simulate: would have placed an order ' . $strategyResult->toJson());
        }
    }

    protected function strategyAdvise(StrategyContract $strategy, Position $position = null)
    {
        $strategyResult = $strategy->advise($position);
        if ($strategyResult->get('result') == 'fail' || $strategyResult->get('result') == 'hold') {
            Log::debug("No advise data available. " . $strategyResult->toJson());

            return;
        }

        $this->placeOrder($strategyResult, $position);
    }

    protected function process()
    {
        $pair        = $this->config->get('pair');
        $cryptoCoin  = $this->config->get('cryptoCoin');
        $fundAccount = $this->config->get('fundAccount');
        $strategy    = $this->config->get('strategy');

        $noExceptions = true;

        $settings = Setting::wherePair($pair)->firstOrFail();

        $this->updateOrdersAndPositions(
            OrderContract::ORDER_SELL,
            PositionContract::POSITION_CLOSE
        );

        $this->updateOrdersAndPositions(
            OrderContract::ORDER_BUY,
            PositionContract::POSITION_OPEN
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
                            Cache::put(
                                'config::fund',
                                (float)number_format($available, 8, '.', ''),
                                1
                            );
                        }
                        if ($fund->getCurrency() == $cryptoCoin) {
                            Cache::put(
                                'config::coin',
                                (float)number_format($available, 8, '.', ''),
                                1
                            );
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
}
