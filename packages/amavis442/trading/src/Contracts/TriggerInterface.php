<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 05-01-18
 * Time: 16:30
 */

namespace Amavis442\Trading\Contracts;


use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Setting;

interface TriggerInterface
{
    const SELL = -1;
    const HOLD = 0;
    const BUY  = 1;

    /**
     * Result is -1, 0, 1 : -1 = sell, 0 = hold, 1 = buy
     *
     *
     * @param float                              $price
     * @param \Amavis442\Trading\Models\Position $position
     * @param array                              $config
     *
     * @return int
     */
    public function signal(float $price, Position $position): int;
}
