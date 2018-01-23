<?php

namespace Amavis442\Trading\Strategies;

use Amavis442\Trading\Contracts\Strategy;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Services\PositionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Class CollectCoins
 *
 * @package Amavis442\Trading\Strategies
 */
class GrowingAndHarvesting implements Strategy
{
    /**
     * @param \Amavis442\Trading\Models\Position|null $position
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function advise(Position $position = null): Collection
    {
        $result = new Collection();
        $result->put('strategy', 'GrowingAndHarvesting');

        $pair = Cache::get('bot::pair');
        $coin = Cache::get('config::coin', 0.0);
        $fund = Cache::get('config::fund', 0.0);
        $currentprice = Cache::get('gdax::' . $pair . '::currentprice', null);
        $config = json_decode(Cache::get('bot::settings'));
        $stradle = Cache::get('bot::stradle', 0.03);

        if (!is_null($currentprice)) {
            $minimalSizeReached = false;
            if ($fund > 0.01 && !is_null($position)) { // Buy and use all of the fund
                $price = $currentprice - (float)$stradle;

                if ($currentprice > $position->open) {
                    $price = $position->open - 0.1;
                }

                $s = $fund / $price;
                if ($s >= 0.001 && $pair == 'BTC-EUR') {
                    $minimalSizeReached = true;
                }

                if ($s >= 0.01 && ($pair == 'ETH-EUR' || $pair == 'LTC-EUR')) {
                    $minimalSizeReached = true;
                }

                $s = (string)$s;
                $size = substr($s, 0,
                    strpos($s, '.') + 9); // should be more then 0.0001 for BTC and 0.01 for ETH and LTC

                if ($minimalSizeReached) {
                    $result->put('side', 'buy');
                    $result->put('size', $size);
                    $result->put('price', number_format($price, 2, '.', ''));

                    return $result->put('result', 'ok');
                } else {
                    $result->put('side', 'hold');
                    $result->put('size', 0);
                    $result->put('price', 0);
                    $result->put('msg', 'Minimum size not reached '.$s);
                    return $result->put('result', 'fail');
                }
            } else {
                $lowerlimit = (float)$config->bottom;
                $upperlimit = (float)$config->top;

                // Needs an indicator to see if the trend goes up or not
                if ($fund <= 0.01 && $coin > 0.0001 && $currentprice > $lowerlimit && $currentprice < $upperlimit) {
                    $price = $currentprice;
                    if (!is_null($position) && !is_null($position->size)) {
                        $size = $position->size;
                    } else {
                        $size = $config->size;
                    }
                    $result->put('side', 'sell');
                    $result->put('size', $size);
                    $result->put('price', number_format($price, 2, '.', ''));

                    return $result->put('result', 'ok');
                }
            }
        }
        $result->put('result', 'hold');

        return $result;
    }

}