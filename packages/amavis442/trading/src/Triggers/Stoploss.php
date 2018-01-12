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
        $buyprice    = $position->open;
        $position_id = $position->id;

        // Hate loss
        $limitStopLoss  = (float) $buyprice * ((100 - $config->stoploss) / 100);
        $profitTreshold = (float) $buyprice * ((100 + $config->takeprofit) / 100);


        $cacheKey           = 'gdax.takeprofit.' . $position_id;
        $oldLimitTakeProfit = Cache::get($cacheKey, 0.0);

        $trailingTakeProfit = (float) $currentprice * ((100 - $config->takeprofit) / 100); // 97 < 100 < 103, Take loss at 97 and lower
        $tp                 = $buyprice + $config->takeprofittreshold;

        Log::info('--- TRIGGER RUN --- ' . $cacheKey);
        Log::info('Currentprice is : ' . $currentprice.', Stoploss limit (loss): ' . $limitStopLoss.', Trailing stop: ' . $trailingTakeProfit);

        if ($trailingTakeProfit < $buyprice) {
            $trailingTakeProfit = $tp;
        }

        if ($trailingTakeProfit > $oldLimitTakeProfit) {
            Log::info('Update trailing stop: from ' . $oldLimitTakeProfit . ' to ' . $trailingTakeProfit);
            Cache::put($cacheKey, $trailingTakeProfit, 30);
        }

        if ($currentprice <= $oldLimitTakeProfit && $currentprice > $tp) {
            Log::warning('Trigger: Profit .... Sell at ' . $currentprice);
            Log::info('--- END TRIGGER RUN ---');
            Cache::forget($cacheKey);
            return TriggerInterface::SELL;
        }

        if ($currentprice < $limitStopLoss) {
            Log::warning('Trigger: Loss .... Sell at ' . $currentprice);
            Log::info('--- END TRIGGER RUN ---');
            Cache::forget($cacheKey);
            return TriggerInterface::SELL;
        }

        if ($currentprice < $buyprice) {
            // reset the counter;
            Cache::put($cacheKey, $tp);
        }

        Log::info('--- END TRIGGER RUN ---');
        return TriggerInterface::HOLD;
    }

}
