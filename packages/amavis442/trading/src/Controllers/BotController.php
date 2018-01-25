<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 25-01-18
 * Time: 19:51
 */

namespace Amavis442\Trading\Controllers;


use Illuminate\Support\Facades\Cache;

class BotController extends Controller
{
    public function heartbeat()
    {
        $hearbeat = Cache::get('bot::heartbeat', null);

        return [$hearbeat];
    }

    public function currentprices()
    {
        $currentPrices = [];
        foreach (['BTC-EUR', 'ETH-EUR', 'LTC-EUR'] as $pair) {
            $currentPrices[] = ['pair' => $pair, 'price' => Cache::get('gdax::' . $pair . '::currentprice')];
        }

        return $currentPrices;
    }
}