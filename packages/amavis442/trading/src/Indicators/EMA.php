<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 17-01-18
 * Time: 15:46
 */

namespace Amavis442\Trading\Triggers;


use Amavis442\Trading\Contracts\Indicator;
use Amavis442\Trading\Contracts\Strategy;
use Illuminate\Support\Collection;

class EMA implements Indicator
{

    public function check(Collection $data): int
    {
        $ema  = trader_ema($data, 20);
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