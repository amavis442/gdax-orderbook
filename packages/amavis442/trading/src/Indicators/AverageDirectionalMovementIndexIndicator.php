<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Amavis442\Trading\Exceptions\NotEnoughDataPointsException;
use Illuminate\Support\Collection;

/**
 * Class AverageDirectionalMovementIndexIndicator
 *
 * @see     http://www.investopedia.com/terms/a/adx.asp
 *
 * The ADX calculates the potential strength of a trend.
 * It fluctuates from 0 to 100, with readings below 20 indicating a weak trend and readings above 50 signaling a strong
 * trend. ADX can be used as confirmation whether the pair could possibly continue in its current trend or not. ADX can
 * also be used to determine when one should close a trade early. For instance, when ADX starts to slide below 50, it
 * indicates that the current trend is possibly losing steam.
 *
 * @package Amavis442\Trading\Indicators
 */
class AverageDirectionalMovementIndexIndicator implements Indicator
{


    public function check(Collection $config): int
    {

        $data = $config->get('data');
        $period = (int)$config->get('period', 14);

        $high = $data['high'];
        $low = $data['low'];
        $close = $data['close'];

        $adx = trader_adx($high, $low, $close, $period);

        throw_unless($adx, NotEnoughDataPointsException::class, "Not enough datapoints");

        $adx = array_pop($adx);

        if ($adx > 50) {
            return static::BUY;
        } elseif ($adx < 20) {
            return static::SELL;
        } else {
            return static::HOLD;
        }
    }

}
