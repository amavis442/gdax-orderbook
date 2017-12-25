<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Coinbase\CoinbaseExchange;
use App\Trade;

class GetTradesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $coinbase = new CoinbaseExchange(config('coinbase.api_key'), config('coinbase.api_secret'), config('coinbase.password'));
        $response = $coinbase->getFills();
        $trades   = [];

        foreach ($response as $orderfilled) {
            $trade = Trade::whereTradeId($orderfilled->trade_id)->first();
            if (!$trade) {
                $trades[$orderfilled->trade_id] = $orderfilled;
            }
        }
        ksort($trades);

        if (count($trades) > 0) {
            foreach ($trades as $trade_id => $tradefilled) {
                $trade             = new Trade();
                $trade->trade_id   = $tradefilled->trade_id;
                $trade->product_id = $tradefilled->product_id;
                $trade->order_id   = $tradefilled->order_id;
                $trade->user_id    = $tradefilled->user_id;
                $trade->profile_id = $tradefilled->profile_id;
                $trade->liquidity  = $tradefilled->liquidity;
                $trade->price      = $tradefilled->price;
                $trade->size       = $tradefilled->size;
                $trade->fee        = $tradefilled->fee;
                $trade->side       = $tradefilled->side;
                $trade->settled    = $tradefilled->settled;
                $trade->usd_volume = $tradefilled->usd_volume;

                $trade->created_at = \Carbon\Carbon::parse($tradefilled->created_at)->format('Y-m-d H:i:s');

                $trade->save();

            }
        }
    }
}
