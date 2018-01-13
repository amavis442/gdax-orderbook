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
use Amavis442\Trading\Contracts\GdaxServiceInterface;


class OrderBot implements BotInterface
{
    public function __construct(GdaxServiceInterface $gdax)
    {
        $this->gdax = $gdax;
    }

    protected function garbageCleanup()
    {
        //$orders = Order::where('order_id', '')->where('status', '<>', 'deleted')->get();
        $orders = Order::whereNull('order_id')->where('status', '<>', 'deleted')->get();
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
        $gdaxOrders = $this->gdax->getOpenOrders();
        if (count($gdaxOrders)) {
            if (is_array($gdaxOrders)) {
                foreach ($gdaxOrders as $gdaxOrder) {
                    $order_id = $gdaxOrder->getId();
                    $order = Order::whereOrderId($order_id)->first();

                    // When open order with order_id is not found then add it.
                    if (is_null($order)) {
                        Order::create(
                            [
                                'pair'     => $gdaxOrder->getProductId(),
                                'side'     => $gdaxOrder->getSide(),
                                'order_id' => $gdaxOrder->getId(),
                                'size'     => $gdaxOrder->getSize(),
                                'amount'   => $gdaxOrder->getPrice(),
                                'status'   => 'Manual',
                            ]);
                    } else {
                        if ($order->status != 'done') {
                            $order->status = $gdaxOrder->getStatus();
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
            $q->whereIn('status', ['open', 'pending']);
            $q->orWhereNull('status');
        })->whereSide('buy')->get();

        if ($orders->count()) {
            foreach ($orders as $order) {
                /** @var \GDAX\Types\Response\Authenticated\Order $gdaxOrder */
                $gdaxOrder = $this->gdax->getOrder($order->order_id);
                $position_id = 0;
                $status = $gdaxOrder->getStatus();

                if ($status) {
                    // A recently placed buy order had been filled so we add it as an open position
                    if ($status == 'done') {
                        $position = Position::create([
                                                         'pair'     => $gdaxOrder->getProductId(),
                                                         'order_id' => $gdaxOrder->getId(),
                                                         'size'     => $gdaxOrder->getSize(),
                                                         'amount'   => $gdaxOrder->getPrice(),
                                                         'open'     => $gdaxOrder->getPrice(),
                                                         'status'   => 'open',
                                                     ]);

                        $position_id = $position->id;
                    }

                    $order->status = $gdaxOrder->getStatus();
                } else {
                    $order->status = $gdaxOrder->getMessage();
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
            $q->whereIn('status', ['open', 'pending']);
            $q->orWhereNull('status');
        })->whereSide('sell')->get();


        if ($orders->count()) {
            foreach ($orders as $order) {
                $gdaxOrder = $this->gdax->getOrder($order->order_id);
                $status = $gdaxOrder->getStatus();

                if ($status) {
                    $order->status = $gdaxOrder->getStatus();
                } else {
                    $order->status = $gdaxOrder->getMessage();
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