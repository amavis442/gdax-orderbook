<?php
declare(strict_types = 1);

namespace Amavis442\Trading\Triggers;

use Amavis442\Trading\Contracts\TriggerInterface;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Description of TrailingSell
 *
 * @see https://www.wikihow.com/Use-a-Trailing-Stop-Loss
 * @author patrickteunissen
 */
class Stoploss implements TriggerInterface
{

    protected $msg = [];

    public function getMessage(): array
    {
        return $this->msg;
    }

    public function signal(float $currentprice, Position $position, Setting $config): int
    {
        $cacheKey    = 'gdax.stoploss.' . $position->id;
        $oldStoploss = Cache::get($cacheKey, 0.0);

        $stoploss = $currentprice - $position->trailingstop;

        if ($stoploss > $oldStoploss) {
            Cache::put($cacheKey, $stoploss, 60);
        } else {
            $stoploss = $oldStoploss;
        }

        if ($currentprice < $stoploss) {
            Log::warning('Trigger: Profit .... Sell at ' . $currentprice);

            return TriggerInterface::SELL;
        }

        return TriggerInterface::HOLD;
    }

}
