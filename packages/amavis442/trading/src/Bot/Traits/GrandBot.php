<?php

namespace Amavis442\Trading\Bot\Traits;

use Amavis442\Trading\Contracts\Exchange;
use Amavis442\Trading\Events\PositionEvent;
use Amavis442\Trading\Models\Order;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Services\OrderService;
use Amavis442\Trading\Services\PositionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
        $side = \Amavis442\Trading\Contracts\Order::ORDER_BUY,
        $positionState = \Amavis442\Trading\Contracts\Position::POSITION_OPEN
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

                    if ($positionState == \Amavis442\Trading\Contracts\Position::POSITION_OPEN) {
                        $position = $this->positionService->open(
                            $exchangeOrder->getProductId(),
                            $exchangeOrder->getId(),
                            $exchangeOrder->getSize(),
                            $exchangeOrder->getPrice()
                        );
                        $positionStatus = $position->status;
                        $order->position_id = $position->id;
                        $order->save();
                    }

                    if ($positionState == \Amavis442\Trading\Contracts\Position::POSITION_CLOSE) {
                        $position_id = $order->position_id;
                        if ($position_id > 0) {
                            $position = $this->positionService->close(
                                $position_id,
                                $exchangeOrder->getSize(),
                                $exchangeOrder->getPrice()
                            );
                            $positionStatus = $position->status;
                        }
                    }

                    if (
                        $positionState == \Amavis442\Trading\Contracts\Position::POSITION_OPEN ||
                        $positionState == \Amavis442\Trading\Contracts\Position::POSITION_CLOSE
                    ) {
                        event(new PositionEvent(
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

    protected function strategyAdvise(
        \Amavis442\Trading\Contracts\Strategy $strategy,
        Position $position = null
    ) {

        $strategyResult = $strategy->advise($position);
        if (
            $strategyResult->get('result') == 'fail' ||
            $strategyResult->get('result') == 'hold'
        ) {
            Log::debug("No advise data available. ". $strategyResult->toJson());
            return;
        }

        $this->placeOrder($strategyResult, $position);
    }
}