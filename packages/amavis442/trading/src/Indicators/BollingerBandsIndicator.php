<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;

/**
 * Class BollingerBandsIndicator
 *
 * This algorithm uses the talib Bollinger Bands function to determine entry entry
 * points for long and sell/short positions.
 *
 * When the price breaks out of the upper Bollinger band, a sell or short position
 * is opened. A long position is opened when the price dips below the lower band.
 *
 * Used to measure the market’s volatility.
 * They act like mini support and resistance levels.
 * Bollinger Bounce
 *
 * A strategy that relies on the notion that price tends to always return to the middle of the Bollinger bands.
 * You buy when the price hits the lower Bollinger band.
 * You sell when the price hits the upper Bollinger band.
 * Best used in ranging markets.
 * Bollinger Squeeze
 *
 * A strategy that is used to catch breakouts early.
 * When the Bollinger bands “squeeze”, it means that the market is very quiet, and a breakout is eminent.
 * Once a breakout occurs, we enter a trade on whatever side the price makes its breakout.
 *
 * @package Amavis442\Trading\Indicators
 */
class BollingerBandsIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = $config->get('data');
        $period = (int)$config->get('period', 10);
        $devup = (int)$config->get('devup', 2);
        $devdn = (int)$config->get('devdn', 2);


        $data2 = $data;
        $current = array_pop($data2['close']);

        $bbands = trader_bbands(
            $data['close'],
            $period,
            $devup,
            $devdn,
            0
        );

        throw_unless($bbands, NotEnoughDataPointsException::class, "Not enough datapoints");

        $upper = $bbands[0];
        $lower = $bbands[2];

        if ($current <= array_pop($lower)) {
            return static::BUY;
        } elseif ($current >= array_pop($upper)) {
            return static::SELL;
        } else {
            return static::HOLD;
        }
    }

}
