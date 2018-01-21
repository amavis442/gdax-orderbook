<?php

namespace Amavis442\Trading\Strategies;

use Amavis442\Trading\Models\Position;
use Illuminate\Support\Collection;

/**
 * Class CollectCoins
 *
 * @package Amavis442\Trading\Strategies
 */
class GrowingAndHarvesting
{
    /*
    Purpose is to get as much coins as possible without looking at the profit/loss element.
    Logic:
        slots = config->get('max_allowed_orders',1) - config->get('placed_open_orders')

        if (slots <= 0)
            if (position->side == 'sell')
                if (order->status != 'NotFound')
                    return null
                else
                    currentprice = config->get('currentPrice')
                    buyprice = position->price
                    if (currentprice < buyprice + 30 euro) return ['side' => 'sell', 'size' => 0.001, 'price' => buyprice + 30]
                    if (currentprice > buyprice + 30 euro) return ['side' => 'sell', 'size' => 0.001, 'price' => currentprice + 0.01]
        else
            if (slots > 0)
                if (account_eur > 0)
                    place buy: return ['side' => 'buy', 'size' => (account / currentprice - 0.01), 'price' => currentprice + 0.01]
                else if (account_btc > 0.0 && account_btc > 0.001)
                    place sell order: return ['side' => 'sell', 'size' => 0.001, 'price' => currentprice + 0.01]
    */

    public function advise(Collection $config, Position $position = null): ?Collection
    {
        $result = new Collection();
        $coin = (float)$config->get('coin',0.0);
        $fund = (float)$config->get('fund',0.0);
        $currentprice = $config->get('currentprice',10000000.00);

        if ($fund > 0.0) { // Buy and use all of the fund
            $price = $currentprice - 0.01;
            $s = $fund / $price;

            $s = (string)$s;

            $size = substr($s,0, strpos($s,'.') + 9); // should be more then 0.0001 for BTC and 0.01 for ETH and LTC

            $result->put('side', 'buy');
            $result->put('size', $size);
            $result->put('price', number_format($price,2,'.',''));

            return $result->put('result', 'ok');
        } else {
            if ($coin > 0.0) {
                $price = $currentprice + 0.01;
                if (!is_null($position) && !is_null($position->size)) {
                    $size = $position->size;
                } else {
                    $size = $config->get('size', 0.001);
                }
                $result->put('side', 'sell');
                $result->put('size', $size);
                $result->put('price', number_format($price,2,'.',''));

                return $result->put('result', 'ok');
            }
        }

        return $result;
    }

}