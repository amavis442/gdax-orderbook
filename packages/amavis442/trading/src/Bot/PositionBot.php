<?php

namespace Amavis442\Trading\Bot;

use Illuminate\Support\Facades\Log;
use Amavis442\Trading\Contracts\BotInterface;
use Amavis442\Trading\Contracts\GdaxServiceInterface;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Setting;
use Amavis442\Trading\Models\Order;

class PositionBot implements BotInterface
{

    protected $gdax;
    protected $stoplossRule;
    protected $msg = [];

    public function __construct(GdaxServiceInterface $gdax)
    {
        $this->gdax = $gdax;
    }

    public function setStopLossService($stoplossRule)
    {
        $this->stoplossRule = $stoplossRule;
    }

    public function getMessage(): array
    {
        return $this->msg;
    }

    /**
     * Check if we have added orders manually and add them to the database.
     */
    public function actualize()
    {
        $gdaxOrders = $this->gdax->getOpenOrders();
        if (count($gdaxOrders)) {
            if (is_array($gdaxOrders)) {
                foreach ($gdaxOrders as $gdaxOrder) {
                    $order_id = $gdaxOrder->getId();
                    $order    = Order::whereOrderId($order_id)->first();

                    // When open order with order_id is not found then add it.                    
                    if (is_null($order)) {
                        Order::create(
                            [
                                'pair'     => $gdaxOrder->getProductId(),
                                'side'     => $gdaxOrder->getSide(),
                                'order_id' => $gdaxOrder->getId(),
                                'size'     => $gdaxOrder->getSize(),
                                'amount'   => $gdaxOrder->getPrice(),
                                'status'   => 'Manual']);
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
        $orders = Order::whereIn('status', ['open', 'pending'])->orWhereNull('status')->whereSide('buy')->get();

        if ($orders->count()) {
            foreach ($orders as $order) {
                /** @var \GDAX\Types\Response\Authenticated\Order $gdaxOrder */
                $gdaxOrder = $this->gdax->getOrder($order->order_id);
                $position_id = 0;
                $status      = $gdaxOrder->getStatus();

                if ($status) {
                    // A recently placed order had been filled so we add it as an open position
                    if ($status == 'done') {
                        $position = Position::create([
                                'pair'     => $gdaxOrder->getProductId(),
                                'order_id' => $gdaxOrder->getId(),
                                'size'     => $gdaxOrder->getSize(),
                                'amount'   => $gdaxOrder->getPrice(),
                                'open'     => $gdaxOrder->getPrice(),
                                'position' => 'open'
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
    public function actualizeSellOrders()
    {
        //$orders = Order::whereNull('status')->whereSide('sell')->get();
            
        $orders = Order::whereIn('status', ['open', 'pending'])->whereSide('sell')->get();

        if ($orders->count()) {
            foreach ($orders as $order) {
                $gdaxOrder = $this->gdax->getOrder($order->order_id);
                $status    = $gdaxOrder->getStatus();

                if ($status) {
                    $order->status = $gdaxOrder->getStatus();
                } else {
                    $order->status = $gdaxOrder->getMessage();
                }
                $order->save();
            }
        }
    }

    /**
     * Find positions that are open (not sold /short) and check if we have a sell order active 
     * or maybe the sell is done. If so close the position
     */
    public function actualizePositions()
    {
        $positions = Position::wherePosition('open')->get();

        if ($positions->count()) {
            foreach ($positions as $position) {
                $order = Order::wherePositionId($position->id)->whereSide('sell')->whereStatus('done')->first();

                if (!is_null($order)) {
                    $position->position = 'closed';
                    $position->save(); // The position is filled so we give it a closed status.
                }
            }
        }
    }

    /**
     * Get the open positions and track the price process and if a trigger comes
     * go short/sell
     */
    protected function watchPositions(float $currentPrice, Setting $config)
    {
        
       
        
        $positions = Position::wherePosition('open')->get();

        if ($positions->count()) {
            Log::info('--- watchPositions ---');

            foreach ($positions as $position) {
                $price       = $position->amount;
                $size        = $position->size;
                $position_id = $position->id;
                $order_id    = $position->order_id; // Buy order_id

                $sellMe = $this->stoplossRule->signal($currentPrice, $position, $config);


                $placeOrder = true;
                if ($sellMe) {
                    $existingSellOrder = Order::wherePositionId($position->id)->whereIn('status', ['open', 'peding'])->first();

                    if ($existingSellOrder) {
                        $placeOrder = false;
                        Log::info('Position ' . $position_id . ' has an open sell order. ');
                    }

                    if ( $placeOrder) {
                        $sellPrice = number_format($currentPrice + 0.01, 2, '.', '');

                        $order = $this->gdax->placeLimitSellOrderFor1Minute($size, $sellPrice);

                        if ($order->getMessage()) {
                            $status = $order->getMessage();
                        } else {
                            $status = $order->getStatus();
                        }
                        Log::info('Place sell order status ' . $status . ' for position ' . $position_id);


                        if ($status == 'open' || $status == 'pending') {
                            Order::create([
                                'pair'        => $order->getProductId(),
                                'side'        => 'sell',
                                'order_id'    => $order->getId(),
                                'size'        => $size,
                                'amount'      => $sellPrice,
                                'position_id' => $position->id
                            ]);

                            Log::info('Place sell order ' . $order->getId() . ' for position ' . $position_id);
                        }
                    }
                }
            }
        }
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

    public function run()
    {
        $config = Setting::select('*')->orderBy('id', 'desc')->first();

        if (is_null($config)) {
            Log::error("No config");
            return;
        }

        // Get Account
        //$account = $this->gdaxService->getAccount('EUR');

        Log::info("=== RUN [" . \Carbon\Carbon::now('Europe/Amsterdam')->format('Y-m-d H:i:s') . "] ===");

        //Cleanup
        $this->garbageCleanup();

        $this->actualizeSellOrders();
        $this->actualizePositions();

        $this->updateBuyOrderStatusAndCreatePosition();

        $botactive = ($config->botactive == 1 ? true : false);
        if (!$botactive) {
            Log::warning("Bot is not active at the moment");
        } else {
            $currentPrice = $this->gdax->getCurrentPrice();
            $this->watchPositions($currentPrice, $config);
        }

        $this->actualizeSellOrders();
        $this->actualizePositions();

        $this->actualize();
    }

}
