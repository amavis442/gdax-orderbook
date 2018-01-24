<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;
use Amavis442\Trading\Exceptions\NotEnoughDataPointsException;

/**
 * Class OnBalanceVolumeIndicator
 *
 * @see http://stockcharts.com/school/doku.php?id=chart_school:technical_indicators:on_balance_volume_obv
 *
 * signal assumption that volume precedes price on confirmation, divergence and breakouts
 * use with mfi to confirm
 *
 * @package Amavis442\Trading\Indicators
 */
class OnBalanceVolumeIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = $config->get('data', []);
        $period = (int)$config->get('period', 14);

        $_obv = trader_obv($data['close'], $data['volume']);

        throw_unless($_obv, NotEnoughDataPointsException::class, "Not enough datapoints");


        $current_obv = array_pop($_obv);
        $prior_obv = array_pop($_obv);
        $earlier_obv = array_pop($_obv);

        if (($current_obv > $prior_obv) && ($prior_obv > $earlier_obv)) {
            return static::BUY;
        } elseif (($current_obv < $prior_obv) && ($prior_obv < $earlier_obv)) {
            return static::SELL;
        } else {
            return static::HOLD;
        }
    }

}
