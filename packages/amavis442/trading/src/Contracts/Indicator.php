<?php

namespace Amavis442\Trading\Contracts;

use Amavis442\Trading\Exceptions\NotEnoughDataPointsException;
use Illuminate\Support\Collection;

interface Indicator
{
    const SELL = -1;
    const HOLD = 0;
    const BUY = 1;

    /**
     * @throws NotEnoughDataPointsException
     * @param Collection $config
     * @return int
     */
    public function check(Collection $config): int;
}
