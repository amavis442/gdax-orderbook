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

    public function __construct(GdaxServiceInterface $gdax)
    {
        $this->gdax = $gdax;
    }

    public function setStopLossService($stoplossRule)
    {
        $this->stoplossRule = $stoplossRule;
    }

    /**
     * Get the open positions and track the price process and if a trigger comes
     * go short/sell
     */
    protected function watch(float $currentPrice, Setting $config)
    {

        $positions = Position::wherePosition('open')->get();

        if ($positions->count()) {
            Log::info('--- watchPositions ---');

            foreach ($positions as $position) {
                $size        = $position->size;
                $position_id = $position->id;

                $sellMe = $this->stoplossRule->signal($currentPrice, $position, $config);

                $placeOrder = true;

                // Are we only watching or do we really wonna place a sell order (stoploss and at what price)
                if ($sellMe && !$position->watch) {
                    $existingSellOrder = Order::wherePositionId($position->id)->whereIn('status', ['open', 'peding'])->first();

                    if ($existingSellOrder) {
                        $placeOrder = false;
                        Log::info('Position ' . $position_id . ' has an open sell order. ');
                    }

                    if ( $placeOrder) {
                        $sellPrice = number_format($position->sellfor);

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
