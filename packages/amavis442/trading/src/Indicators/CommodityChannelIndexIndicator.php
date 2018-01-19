<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;

/**
 * Class CommodityChannelIndexIndicator
 * @package Amavis442\Trading\Indicators
 */
class CommodityChannelIndexIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = (array)$config->get('data', []);
        $period = (int)$config->get('period', 14);

        $cci = trader_cci($data['high'], $data['low'], $data['close'], $period);

        throw_unless($cci, NotEnoughDataPointsException::class, "Not enough datapoints");

        $cci = array_pop($cci);

        if ($cci > 100) {
            return static::SELL;
        } elseif ($cci < -100) {
            return static::BUY;
        } else {
            return static::HOLD;
        }
    }

}
