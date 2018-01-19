<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 09-01-18
 * Time: 17:11
 */

namespace Amavis442\Trading\Contracts;

use Amavis442\Trading\Exceptions\NotEnoughDataPointsException;
use Illuminate\Support\Collection;

interface Indicator
{
    const SELL = -1;
    const HOLD = 0;
    const BUY  = 1;

    /**
     * @throws NotEnoughDataPointsException
     * @param Collection $config
     * @return int
     */
    public function check(Collection $config): int;
}