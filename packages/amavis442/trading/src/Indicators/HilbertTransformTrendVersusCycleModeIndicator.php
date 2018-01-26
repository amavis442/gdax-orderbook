<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;
use Amavis442\Trading\Exceptions\NotEnoughDataPointsException;

/**
 * Class HilbertTransformTrendVersusCycleModeIndicator
 *
 * Simply tell us if the market is
 * either trending or cycling, with an additional parameter the method returns
 * the number of days we have been in a trend or a cycle.
 *
 * @package Amavis442\Trading\Indicators
 */
class HilbertTransformTrendVersusCycleModeIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = $config->get('data', []);
        $numperiods = (bool)$config->get('numperiods', false);


        $a_htm = trader_ht_trendmode($data['close']);

        throw_unless($a_htm, NotEnoughDataPointsException::class, "Not enough datapoints");

        $htm = array_pop($a_htm);

        /**
         *  We can return the number of periods we have been
         *  in either a trend or a cycle by calling this again with
         *  $numperiods == true
         */
        if ($numperiods) {
            $nump = 1;
            $test = $htm;
            for ($b = 0; $b < count($a_htm); $b++) {
                $test = array_pop($a_htm);
                if ($test == $htm) {
                    $nump++;
                } else {
                    break;
                }
            }
            return $nump;
        }

        /**
         *  Otherwise we just return if we are in a trend or not.
         */
        if ($htm == 1) {
            return 1; // we are in a trending mode
        }

        return 0; // we are cycling.
    }
}
