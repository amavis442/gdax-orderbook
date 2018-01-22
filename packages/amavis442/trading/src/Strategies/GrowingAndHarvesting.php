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
    /**
     * @param \Illuminate\Support\Collection          $config
     * @param \Amavis442\Trading\Models\Position|null $position
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function advise(Collection $config, Position $position = null): ?Collection
    {
        $result = new Collection();
        $coin = (float)$config->get('coin', 0.0);
        $fund = (float)$config->get('fund', 0.0);
        $currentprice = $config->get('currentprice', 10000000.00);

        $minimalSizeReached = false;
        if ($fund > 0.01) { // Buy and use all of the fund
            $price = $currentprice - 0.01;
            $s = $fund / $price;
            if ($s >= 0.001) {
                $minimalSizeReached = true;
            }

            $s = (string)$s;

            $size = substr($s, 0, strpos($s, '.') + 9); // should be more then 0.0001 for BTC and 0.01 for ETH and LTC
            if ($minimalSizeReached) {
                $result->put('side', 'buy');
                $result->put('size', $size);
                $result->put('price', number_format($price, 2, '.', ''));

                return $result->put('result', 'ok');
            } else {
                $result->put('side', 'hold');
                $result->put('size', 0);
                $result->put('price', 0);

                return $result->put('result', 'fail');
            }
        } else {
            if ($coin > 0.0001 && $currentprice > (float)config('trading.lowerlimit') && $currentprice < (float)config('trading.upperlimit')) {
                $price = $currentprice;
                if (!is_null($position) && !is_null($position->size)) {
                    $size = $position->size;
                } else {
                    $size = $config->get('size', 0.001);
                }
                $result->put('side', 'sell');
                $result->put('size', $size);
                $result->put('price', number_format($price, 2, '.', ''));

                return $result->put('result', 'ok');
            }
        }
        $result->put('result', 'hold');
        return $result;
    }

}