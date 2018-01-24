<?php

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Illuminate\Support\Collection;
use Amavis442\Trading\Exceptions\NotEnoughDataPointsException;

/**
 * Class MoneyFlowIndexIndicator
 *
 * The money flow index (MFI) is a momentum indicator that measures the inflow and
 * outflow of money into a security over a specific period of time. The MFI uses a
 * stock's price and volume to measure trading pressure. Because the MFI adds trading
 * volume to the relative strength index (RSI), it's sometimes referred to as volume-weighted RSI.
 *
 * @see https://www.investopedia.com/terms/m/mfi.asp
 *
 * @package Amavis442\Trading\Indicators
 */
class MoneyFlowIndexIndicator implements Indicator
{
    public function check(Collection $config): int//public function run(Collection $data, int $period = 14): int
    {

        $data = $config->get('data', []);
        $period = (int)$config->get('period', 14);


        $mfi = trader_mfi(
            $data['high'],
            $data['low'],
            $data['close'],
            $data['volume'],
            $period
        );

        throw_unless($mfi, NotEnoughDataPointsException::class, "Not enough datapoints");

        $mfiValue = array_pop($mfi);

        if ($mfiValue < -10) {
            return static::BUY;
        } elseif ($mfiValue > 80) {
            return static::SELL;
        }

        return static::HOLD;
    }

}