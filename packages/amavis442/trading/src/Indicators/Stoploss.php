<?php
declare(strict_types=1);

namespace Amavis442\Trading\Indicators;

use Amavis442\Trading\Contracts\Indicator;
use Amavis442\Trading\Models\Position;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Description of TrailingSell
 *
 * @see https://www.wikihow.com/Use-a-Trailing-Stop-Loss
 * @author patrickteunissen
 */
class Stoploss implements Indicator
{

    public function check(Collection $config): int
    {
        $currentprice = Cache::get('gdax::' . $config->get('pair') . '::currentprice');
        $position = $config->get('position');

        $cacheKey = 'gdax::stoploss::' . $position->id;
        $oldStoploss = Cache::get($cacheKey, 0.0);

        $stoploss = $currentprice - $position->trailingstop;

        if ($stoploss > $oldStoploss) {
            Cache::put($cacheKey, $stoploss, 60);
        } else {
            $stoploss = $oldStoploss;
        }

        if ($currentprice < $stoploss) {
            Log::warning('Trigger: Profit .... Sell at ' . $currentprice);

            return Indicator::SELL;
        }

        return Indicator::HOLD;
    }
}
