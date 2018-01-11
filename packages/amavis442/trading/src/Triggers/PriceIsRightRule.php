<?php
declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 05-01-18
 * Time: 16:20
 */

namespace Amavis442\Trading\Triggers;


use Amavis442\Trading\Contracts\TrgiierInterface;

class PriceIsRightRule implements TriggerInterface
{
    public function validate(float $price, float $spread, ?float $lowestBuyPrice = null, ?float $highestBuyPrice = null, ?float $lowestSellPrice = null,?float $highestSellPrice = null): bool
    {
        $canPlaceBuyOrder = false;
        if ($lowestBuyPrice || $lowestSellPrice) {

            if ($lowestBuyPrice && !$lowestSellPrice) {
                if ($price < ($lowestBuyPrice - $spread)) {
                    $canPlaceBuyOrder = true;
                }
            }
            
            if (!$lowestBuyPrice && $lowestSellPrice) {
                if ($price < ($lowestSellPrice - $spread)) {
                    $canPlaceBuyOrder = true;
                }
            }

            if ($lowestBuyPrice && $lowestSellPrice) {
                if ($price < ($lowestBuyPrice - $spread)) {
                    $canPlaceBuyOrder = true;
                }
            }
        }

        if (!$lowestBuyPrice && !$highestBuyPrice && !$lowestSellPrice) { // First order of the day
            $canPlaceBuyOrder = true;
        }

        return $canPlaceBuyOrder;
    }
}
