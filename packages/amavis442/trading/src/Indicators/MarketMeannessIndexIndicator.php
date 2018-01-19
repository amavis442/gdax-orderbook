<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;

/**
 * Class MarketMeannessIndexIndicator
 *
 * This indicator is not a measure of how
 * grumpy the market is, it shows if we are currently in or out of a trend
 * based on price reverting to the mean.
 *
 * NO TALib specific function
 *
 * if mmi > 75 then not trending
 * if mmi < 75 then trending
 *
 * @package Amavis442\Trading\Indicators
 */
class MarketMeannessIndexIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = (array)$config->get('data', []);
        $period = (int)$config->get('period', 200);


        $data_close = [];
        foreach ($data['close'] as $point) {
            $data_close[] = $point;
        }
        
        $nl     = $nh     = 0;
        $len    = count($data_close);
        $median = (array_sum($data_close) / $len);
        
        for ($a = 0; $a < $len; $a++) {
            if ($data_close[$a] > $median && $data_close[$a] > @$data_close[$a - 1]) {
                $nl++;
            } elseif ($data_close[$a] < $median && $data_close[$a] < @$data_close[$a - 1]) {
                $nh++;
            }
        }
        
        $mmi = 100. * ($nl + $nh) / ($len - 1);
        if ($mmi < 75) {
            return static::BUY;
        }
        
        if ($mmi > 75) {
            return static::SELL;
        }

        return static::HOLD;
    }

}
