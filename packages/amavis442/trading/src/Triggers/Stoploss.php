<?php
declare(strict_types=1);

namespace Amavis442\Trading\Triggers;

use Amavis442\Trading\Util\Cache;

/**
 * Description of TrailingSell
 *
 * @author patrickteunissen
 */
class Stoploss
{
    protected $msg = [];

    public function getMessage(): array
    {
        return $this->msg;
    }

    /**
     *
     * @see https://www.investopedia.com/video/play/how-use-trailing-stops/
     *
     * @param float $currentprice
     * @param float $buyprice
     * @param float $stoplossPercentage
     */
    public function trailingStop(int $position_id, float $currentprice, float $buyprice, float $stoplossPercentage = 3): bool
    {
        $stoploss = (float)($stoplossPercentage / 100);
        // Hate loss
        $limitLoss      = $buyprice * (1 - $stoploss);
        $profitTreshold = $buyprice * (1 + $stoploss);
        $oldLimit       = Cache::get('gdax.stoploss.' . $position_id, null);

        $limit = (float)$currentprice * (1 - $stoploss); // 97 < 100 < 103, Take loss at 97 and lower
        Cache::put('gdax.stoploss.' . $position_id, $limit, 3600);

        $this->msg[] = '<info>Currentprice: ' . $currentprice . ',Bought: ' . $buyprice . ', Limit: ' . $limit . ', Oldlimit: ' . $oldLimit . ", Limit loss: " . $limitLoss . ", Profit treshold: " . $profitTreshold . ", Stoploss: " . $stoploss . '</info>';

        // Take profit
        if ($limit > $buyprice && $currentprice < $oldLimit) {
            $this->msg[] = '<comment>*** Trigger: Profit .... Sell at ' . $currentprice . "</comment>";

            return true;
        }

        if ($currentprice < $limitLoss) {
            $this->msg[] = '<error>*** Trigger: Loss .... Sell at ' . $currentprice . "</error>";

            return true;
        }

        return false;
    }
}
