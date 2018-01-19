<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;

/**
 * Class HilbertTransformSinewaveIndicator
 *
 * We are actually using DSP
 * on the prices to attempt to get a lag-free/low-lag indicator.
 * This indicator can be passed an extra parameter and it will tell you in
 * we are in a trend or not. (when used as an indicator do not use in a trending market)
 *
 * @package Amavis442\Trading\Indicators
 */
class HilbertTransformSinewaveIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = (array)$config->get('data', []);
        $trend = (bool)$config->get('trend', false);

        $hts = trader_ht_sine($data['open'], $data['close']);

        throw_unless($hts, NotEnoughDataPointsException::class, "Not enough datapoints");

        $dcsine = array_pop($hts[1]);
        $p_dcsine = array_pop($hts[1]);
        $leadsine = array_pop($hts[0]);
        $p_leadsine = array_pop($hts[0]);

        if ($trend) {
            if ($dcsine < 0 && $p_dcsine < 0 && $leadsine < 0 && $p_leadsine < 0) {
                return static::BUY; // uptrend
            }

            if ($dcsine > 0 && $p_dcsine > 0 && $leadsine > 0 && $p_leadsine > 0) {
                return static::SELL; // downtrend
            }

            return static::HOLD;
        }

        if ($leadsine > $dcsine && $p_leadsine <= $p_dcsine) {
            return static::BUY;
        }
        if ($leadsine < $dcsine && $p_leadsine >= $p_dcsine) {
            return static::SELL;
        }

        return static::HOLD;
    }

}
