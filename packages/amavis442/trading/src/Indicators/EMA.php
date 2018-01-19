<?php

namespace Amavis442\Trading\Triggers;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;

/**
 * Class EMA
 * @package Amavis442\Trading\Triggers
 */
class EMA implements Indicator
{

    public function check(Collection $config): int
    {
        $data = (array)$config->get('data', []);
        $period = (int)$config->get('period', 20);

        $ema  = trader_ema($data, $period);

        throw_unless($ema, NotEnoughDataPointsException::class, "Not enough datapoints");

        $ema  = @array_pop($ema) ?? 0;

        $cand = $this->candle_value();

        $current_price  = array_pop($data['close']);
        $previous_price = array_pop($data['close']);

        if ($cand['current']['indecision'] > 0) {
            return 0;
        }
        if ($cand['current']['reverse_bear'] > 60 && $current_price > $ema && $previous_price < $ema) {
            return 1;
        }
        if ($cand['current']['reverse_bull'] > 60 && $current_price < $ema && $previous_price > $ema) {
            return -1;
        }
        return 0;
    }
}