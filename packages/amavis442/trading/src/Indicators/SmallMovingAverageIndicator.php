<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Amavis442\Trading\Exceptions\NotEnoughDataPointsException;
use Illuminate\Support\Collection;

/**
 * Class SmallMovingAverageIndicator
 *
 * @package Amavis442\Trading\Indicators
 */
class SmallMovingAverageIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = $config->get('data');
        $period = (int)$config->get('period', 14);
        $k1 = $data->pluck('close')->all();

        $sma = trader_sma($k1, 14);

        throw_unless($sma, NotEnoughDataPointsException::class, "Not enough datapoints");

        $sma = array_pop($sma);

        if ($sma > 50) {
            return static::BUY;
        } elseif ($sma < 20) {
            return static::SELL;
        } else {
            return static::HOLD;
        }
    }
}
