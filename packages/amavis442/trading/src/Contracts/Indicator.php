<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 09-01-18
 * Time: 17:11
 */

namespace Amavis442\Trading\Contracts;


use Illuminate\Support\Collection;

interface Indicator
{
    const SELL = -1;
    const HOLD = 0;
    const BUY  = 1;

    public function check(Collection $data): int;
}