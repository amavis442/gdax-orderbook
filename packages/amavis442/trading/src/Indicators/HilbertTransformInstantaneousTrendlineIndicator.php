<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;

/**
 * Class HilbertTransformInstantaneousTrendlineIndicator
 *
 * If the price moves 1.5% away from the trendline we can declare a trend.
 *
 *
 * WMA(4)
 *
 * if WMA(4) < htl for five periods then in downtrend (sell in trend mode)
 * if WMA(4) > htl for five periods then in uptrend   (buy in trend mode)
 *
 * // if price is 1.5% more than trendline, then  declare a trend
 * (WMA(4)-trendline)/trendline >= 0.15 then trend = 1
 *
 *
 * @package Amavis442\Trading\Indicators
 */
class HilbertTransformInstantaneousTrendlineIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = (array)$config->get('data', []);
        $period = (int)$config->get('period', 4);

        $declared = $uptrend = $downtrend = 0;
        $a_htl = $a_wma4 = [];
        $htl = trader_ht_trendline($data['close']);

        throw_unless($htl, NotEnoughDataPointsException::class, "Not enough datapoints");

        $wma4 = trader_wma($data['close'], $period);

        throw_unless($wma4, NotEnoughDataPointsException::class, "Not enough datapoints");


        for ($a = 0; $a < 5; $a++) {
            $a_htl[$a] = array_pop($htl);
            $a_wma4[$a] = array_pop($wma4);
            $uptrend += ($a_wma4[$a] > $a_htl[$a] ? 1 : 0);
            $downtrend += ($a_wma4[$a] < $a_htl[$a] ? 1 : 0);

            $declared = (($a_wma4[$a] - $a_htl[$a]) / $a_htl[$a]);
        }


        if ($uptrend || $declared >= 0.15) {
            return static::BUY;
        }

        if ($downtrend || $declared <= 0.15) {
            return static::SELL;
        }

        return static::HOLD;
    }

}
