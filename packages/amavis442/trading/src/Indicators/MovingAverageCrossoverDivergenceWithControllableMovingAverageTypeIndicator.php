<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;

/**
 * Class MovingAverageCrossoverDivergenceWithControllableMovingAverageTypeIndicator
 *
 * MACD indicator with controllable types and tweakable periods.
 *
 * @package Amavis442\Trading\Indicators
 */
class MovingAverageCrossoverDivergenceWithControllableMovingAverageTypeIndicator implements Indicator
{

    public function check(Collection $config): int
    {
        $data = (array)$config->get('data', []);
        $fastPeriod = (int)$config->get('fastPeriod', 12);
        $fastMAType = (int)$config->get('fastMAType', 0);
        $slowPeriod = (int)$config->get('slowPeriod', 26);
        $slowMAType = (int)$config->get('slowMAType', 0);
        $signalPeriod = (int)$config->get('signalPeriod', 9);
        $signalMAType = (int)$config->get('signalMAType', 0);

        $fastMAType = $this->ma_type($fastMAType);
        $slowMAType = $this->ma_type($slowMAType);
        $signalMAType = $this->ma_type($signalMAType);


        $macd = trader_macdext(
            $data['close'],
            $fastPeriod,
            $fastMAType,
            $slowPeriod,
            $slowMAType,
            $signalPeriod,
            $signalMAType
        );

        if (!empty($macd)) {
            $macd = array_pop($macd[0]) - array_pop($macd[1]);

            if ($macd < 0) {
                return static::SELL;

            } elseif ($macd > 0) {
                return static::BUY;
            } else {
                return static::HOLD;
            }
        }

        return -2;
    }

}
