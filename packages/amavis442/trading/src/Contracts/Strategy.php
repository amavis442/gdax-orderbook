<?php

namespace Amavis442\Trading\Contracts;

use Illuminate\Support\Collection;
use Amavis442\Trading\Models\Position;

interface Strategy {

    public function advise(Position $position = null): Collection;

}
