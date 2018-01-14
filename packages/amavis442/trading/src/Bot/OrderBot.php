<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 13-01-18
 * Time: 15:13
 */

namespace Amavis442\Trading\Bot;

use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Order;

use Illuminate\Support\Facades\Log;
use Amavis442\Trading\Contracts\BotInterface;
use Amavis442\Trading\Contracts\ExchangeInterface;


class OrderBot implements BotInterface
{
    public function __construct(ExchangeInterface $exchange)
    {
        $this->exchange = $exchange;
    }

    protected function garbageCleanup()
    {
        $orders = Order::where(function($q){
            $q->whereNull('order_id');
            $q->orWhere('order_id','');
        })->where('status', '<>', 'deleted')->get();

        if ($orders->count()) {
            foreach ($orders as $order) {
                $order->status = 'deleted';
                $order->save();
            }
        }
    }

    /**
     * Check if we have added orders manually and add them to the database.
     */
    public function updateOpenOrders()
    {
        $exchangeOrders = $this->exchange->getOpenOrders();
        if (count($exchangeOrders)) {
            if (is_array($exchangeOrders)) {
                foreach ($exchangeOrders as $exchangeOrder) {
                    $order_id = $exchangeOrder->getId();
                    $order = Order::whereOrderId($order_id)->first();

                    // When open order with order_id is not found then add it.
                    if (is_null($order)) {
                        Order::create(
                            [
                                'pair'     => $exchangeOrder->getProductId(),
                                'side'     => $exchangeOrder->getSide(),
                                'order_id' => $exchangeOrder->getId(),
                                'size'     => $exchangeOrder->getSize(),
                                'amount'   => $exchangeOrder->getPrice(),
                                'status'   => 'manual',
                            ]);
                    } else {
                        if ($order->status != 'done') {
                            $order->status = $exchangeOrder->getStatus();
                            $order->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * If we have open buy order that can be filled during the looptime
     * then update the buy order and open a position when the buy is filled (done)
     *
     * Update the open buys
     */
    public function updateBuyOrderStatusAndCreatePosition()
    {
        $orders = Order::where(function ($q) {
            $q->whereIn('status', ['open', 'pending','manual']);
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
                    if ($status == 'done') {
                        $position = Position::create([
                                                         'pair'     => $exchangeOrder->getProductId(),
                                                         'order_id' => $exchangeOrder->getId(),
                                                         'size'     => $exchangeOrder->getSize(),
                                                         'amount'   => $exchangeOrder->getPrice(),
                                                         'open'     => $exchangeOrder->getPrice(),
                                                         'status'   => 'open',
                                                     ]);

                        $position_id = $position->id;
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


    /**
     * Update the open Sells to see if they are filled (done)
     * If so update the order.
     */
    public function updateSellOrdersAndClosePosition()
    {
        $orders = \Amavis442\Trading\Models\Order::where(function ($q) {
            $q->whereIn('status', ['open', 'pending','manual']);
            $q->orWhereNull('status');
        })->whereSide('sell')->get();


        if ($orders->count()) {
            foreach ($orders as $order) {
                $exchangeOrder = $this->exchange->getOrder($order->order_id);
                $status = $exchangeOrder->getStatus();

                if ($status) {
                    $order->status = $exchangeOrder->getStatus();
                } else {
                    $order->status = $exchangeOrder->getMessage();
                }
                $order->save();

                if ($order->status == 'done') {
                    // Check if the sell order has a position and if so, close the position
                    $position_id = $order->position_id;
                    if ($position_id > 0) {
                        $position = Position::find($position_id);
                        $position->close = $order->amount;
                        $position->status = 'closed';
                        $position->save();
                    }
                }
            }
        }
    }

    public function run()
    {
        $this->garbageCleanup();
        $this->updateOpenOrders();
        $this->updateBuyOrderStatusAndCreatePosition();
        $this->updateSellOrdersAndClosePosition();

    }
}