<?php
declare(strict_types=1);

namespace Amavis442\Trading\Triggers;

use Amavis442\Trading\Contracts\TriggerInterface;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Setting;
use Illuminate\Support\Facades\Cache;

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
        $timestamp = \Carbon\Carbon::now('Europe/Amsterdam')->format('Y-m-d H:i:s');


        $stoploss   = (float)($config->stoploss / 100);
        $takeprofit = (float)($config->takeprofit / 100);

        $buyprice    = $position->open;
        $position_id = $position->id;

        // Hate loss
        $limitStopLoss  = (float)$buyprice * (1 - $stoploss);
        $profitTreshold = $buyprice * (1 + $takeprofit);

        $oldLimitTakeProfit = Cache::get('gdax.takeprofit.' . $position_id, 0);

        $trailingTakeProfit = (float)$currentprice * (1 - $takeprofit); // 97 < 100 < 103, Take loss at 97 and lower

        $this->msg[] = $timestamp . ' .... <info>Bought: ' . $buyprice . '</info>';
        $this->msg[] = $timestamp . ' .... <info>Currentprice: ' . $currentprice . '<info>';
        $this->msg[] = $timestamp . ' .... <info>Stoploss limit: ' . $limitStopLoss . '</info>';
        $this->msg[] = $timestamp . ' .... <info>Profit treshold: ' . $profitTreshold . '</info>';
        $this->msg[] = $timestamp . ' .... <info>Old Trailing stop: ' . $oldLimitTakeProfit . '</info>';
        $this->msg[] = $timestamp . ' .... <info>Trailing stop: ' . $trailingTakeProfit . '</info>';


        if ($trailingTakeProfit > $oldLimitTakeProfit) {
            $this->msg[] = $timestamp . ' .... <info>Update trailing stop: from ' . $oldLimitTakeProfit . ' to ' . $trailingTakeProfit . '</info>';
            Cache::put('gdax.takeprofit.' . $position_id, $trailingTakeProfit, 3600);
        } else {
            if ($currentprice > $oldLimitTakeProfit && $currentprice > $profitTreshold) {
                $this->msg[] = $timestamp . ' .... <comment>*** Trigger: Profit .... Sell at ' . $currentprice . "</comment>";

                return TriggerInterface::SELL;
            } else {
                if ($currentprice < $limitStopLoss) {
                    $this->msg[] = $timestamp . ' .... <error>*** Trigger: Loss .... Sell at ' . $currentprice . "</error>";

                    return TriggerInterface::SELL;
                }
            }
        }

        return TriggerInterface::HOLD;
    }
}
