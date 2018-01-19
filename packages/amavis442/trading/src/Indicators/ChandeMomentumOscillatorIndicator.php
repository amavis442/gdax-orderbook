<?php
namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;

/**
 * Class ChandeMomentumOscillatorIndicator
 * @package Amavis442\Trading\Indicators
 */
class ChandeMomentumOscillatorIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = (array)$config->get('data', []);
        $period = (int)$config->get('period', 14);

        $cmo = trader_cmo($data['close'], $period);

        throw_unless($cmo, NotEnoughDataPointsException::class, "Not enough datapoints");

        $cmo = array_pop($cmo);

        if ($cmo > 50) {
            return static::SELL;
        } elseif ($cmo < -50) {
            return static::BUY;
        } else {
            return static::HOLD;
        }
    }

}
