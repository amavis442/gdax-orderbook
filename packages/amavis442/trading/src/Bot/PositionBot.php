<?php

namespace Amavis442\Trading\Bot;

use Amavis442\Trading\Database\Seeder\PositionTableSeeder;
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
        $positions = Position::whereStatus('trailing')->get();

        if ($positions->count()) {
            Log::info('--- watchPositions ---');

            foreach ($positions as $position) {

                $sellMe = $this->stoplossRule->signal($currentPrice, $position, $config);
                $placeOrder = true;

                // Are we only watching or do we really wonna place a sell order (stoploss and at what price)
                if ($sellMe && !$position->watch) {
                    $existingSellOrder = Order::wherePositionId($position->id)->whereIn('status', ['open', 'pending'])->first();

                    if ($existingSellOrder) {
                        $placeOrder = false;
                        Log::info('Position ' . $position->id . ' has an open sell order. ');
                    }

                    if ( $placeOrder) {
                        $sellPrice = number_format($currentPrice + 0.01,2,'.',''); // Spread of 0.01. If the spread is higher the sell will be rejected
                        $this->sellPosition($sellPrice, $position,'limit');
                    }
                }
            }
        }
    }

    public function sellPosition(float $sellPrice, Position $position, $sellType = 'limit', $realSell = false): bool
    {
        $size = $position->size;
        $status = '';

        if ($realSell) {
            switch ($sellType) {
                case 'limit':
                    $order = $this->gdax->placeLimitSellOrderFor1Minute($size, $sellPrice);
                    break;
                case 'market':
                    break;
                case 'stop':
                    break;
            }

            if ($order->getMessage()) {
                $status = $order->getMessage();
            } else {
                $status = $order->getStatus();
            }
            Log::info('Place sell order status ' . $status . ' for position ' . $position->id);


            if ($status == 'open' || $status == 'pending') {
                Order::create([
                                  'pair'        => $order->getProductId(),
                                  'side'        => 'sell',
                                  'order_id'    => $order->getId(),
                                  'size'        => $size,
                                  'amount'      => $sellPrice,
                                  'position_id' => $position->id
                              ]);

                Log::info('Place sell order ' . $order->getId() . ' for position ' . $position->id);

                return true;
            }
        }

        // For testing/simulating
        if (!$realSell) {
            Order::create([
                              'pair'        => $position->pair,
                              'side'        => 'sell',
                              'order_id'    => 'simulate',
                              'size'        => $size,
                              'amount'      => $sellPrice,
                              'position_id' => $position->id,
                              'status'      => 'simulate'
                          ]);

            Log::info('Place sell order [simulate] for position ' . $position->id);
            return true;
        }

        return false;
    }

    public function setTrailing(Position $position)
    {
       $position->status = 'trailing';
       $position->save();
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
    }

}
