<?php

namespace Amavis442\Trading\Controllers;

use Illuminate\Support\Facades\Cache;

class BotController extends Controller
{
    public function heartbeat()
    {
        $hearbeat = Cache::get('bot::heartbeat', null);

        return ['heartbeat' => $hearbeat];
    }

    public function currentprices()
    {
        $currentPrices = [];
        foreach (['BTC-EUR', 'ETH-EUR', 'LTC-EUR'] as $pair) {
            $price = Cache::get('gdax::' . $pair . '::currentprice');
            $currentPrices[] = [
                'pair' => $pair,
                'currentprice' => number_format($price, 2, '.', '')
            ];
        }

        return ['currentprices' => $currentPrices];
    }
}
