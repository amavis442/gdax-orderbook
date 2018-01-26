<?php

namespace Amavis442\Trading\Contracts;

interface Candle
{
    public function high(): float;

    public function low(): float;

    public function open(): float;

    public function close(): float;

    public function volume(): float;
}
