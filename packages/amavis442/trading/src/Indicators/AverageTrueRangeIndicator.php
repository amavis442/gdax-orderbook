<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;
use Amavis442\Trading\Exceptions\NotEnoughDataPointsException;

/**
 * Class AverageTrueRangeIndicator
 *
 * http://www.investopedia.com/articles/trading/08/atr.asp
 *
 * The idea is to use ATR to identify breakouts, if the price goes higher than
 * the previous close + ATR, a price breakout has occurred.
 *
 * The position is closed when the price goes 1 ATR below the previous close.
 *
 * This algorithm uses ATR as a momentum strategy, but the same signal can be used for
 * a reversion strategy, since ATR doesn't indicate the price direction (like adx below)
 *
 * @package Amavis442\Trading\Indicators
 */
class AverageTrueRangeIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = $config->get('data');
        $period = (int)$config->get('period', 14);

        $close  = $data['close'];
        $high = $data['high'];
        $low = $data['low'];

        if ($period > count($close)) {
            $period = round(count($close) / 2);
        }

        $data2      = $close;
        $current    = array_pop($data2); // we assume this is current
        $prev_close = array_pop($data2); // prior close

        $atr = trader_atr(
            $high,
            $low,
            $close,
            $period
        );

        throw_unless($atr, NotEnoughDataPointsException::class, "Not enough datapoints");

        $atr = array_pop($atr);
        
        // An upside breakout occurs when the price goes 1 ATR above the previous close
        $upside_signal = ($current - ($prev_close + $atr));

        // A downside breakout occurs when the previous close is 1 ATR above the price
        $downside_signal = ($prev_close - ($current + $atr));

        if ($upside_signal > 0) {
            return static::BUY;
        } elseif ($downside_signal > 0) {
            return static::SELL;
        }

        return static::HOLD;
    }
}
