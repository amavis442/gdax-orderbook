<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 09-01-18
 * Time: 10:41
 */

namespace Amavis442\Trading\Bot;

use Amavis442\Trading\Contracts\BotInterface;
use Amavis442\Trading\Traits\OHLC;
use Amavis442\Trading\Util\PositionConstants;

class BuyBot implements BotInterface
{
    protected $container;
    protected $config;

    /**
     * @var \Amavis442\Trading\Contracts\ExchangeInterface
     */
    protected $exchange;

    /**
     * @var \Amavis442\Trading\Contracts\OrderServiceInterface;
     */
    protected $orderService;

    /**
     * @var \Amavis442\Trading\Contracts\StrategyInterface;
     */
    protected $settingsService;

    protected $msg = [];

s    /**
     * Factory
     *
     * @return \Amavis442\Trading\Commands\active
     */
    protected function getStrategy()
    {
        return $this->container->get('bot.strategy');
    }

    protected function getRule($side)
    {
        return $this->container->get('bot.' . $side . '.rule');
    }


    protected function placeBuyOrder($size, $price): bool
    {
        $positionCreated = false;

        $order = $this->exchange->placeLimitBuyOrder($size, $price);

        if ($order->getId() && ($order->getStatus() == \GDAX\Utilities\GDAXConstants::ORDER_STATUS_PENDING || $order->getStatus() == \GDAX\Utilities\GDAXConstants::ORDER_STATUS_OPEN)) {
            $this->orderService->buy($order->getId(), $size, $price);
            $positionCreated = true;
        } else {
            $reason = $order->getMessage() . $order->getRejectReason() . ' ';
            $this->orderService->insertOrder('buy', 'rejected', $size, $price, $reason);
        }

        return $positionCreated;
    }

    protected function init()
    {
        $this->orderService    = $this->container->get('bot.service.order');
        $this->gdaxService     = $this->container->get('bot.service.gdax');
        $this->positionService = $this->container->get('bot.service.position');
    }


    public function run()
    {
        $this->init();
        $msg = [];

        $strategy = $this->getStrategy();
        $buyRule  = $this->getRule('buy');

        // Even when the limit is reached, i want to know the signal
        $signal = $strategy->getSignal();
        $msg    = array_merge($msg, $strategy->getMessage());


        $numOpenOrders        = (int)$this->orderService->getNumOpenBuyOrders() + (int)$this->positionService->getNumOpen();
        $numOrdersLeftToPlace = (int)$this->config['max_orders'] - $numOpenOrders;
        if (!$numOrdersLeftToPlace) {
            $numOrdersLeftToPlace = 0;
        }

        $botactive = ($this->config['botactive'] == 1 ? true : false);
        if (!$botactive) {
            $msg[] = 'Bot is disabled';
        } else {

            $currentPrice = $this->gdaxService->getCurrentPrice();

            // Create safe limits
            $topLimit    = $this->config['top'];
            $bottomLimit = $this->config['bottom'];

            if (!$currentPrice || $currentPrice < 1 || $currentPrice > $topLimit || $currentPrice < $bottomLimit) {
                $msg[] = sprintf("Treshold reached %s  [%s]  %s so no buying for now.", $bottomLimit, $currentPrice, $topLimit);
            } else {
                if ($signal == PositionConstants::BUY && $numOrdersLeftToPlace > 0) {

                    $size     = $this->config['size'];
                    $buyPrice = number_format($currentPrice - 0.01, 2, '.', '');
                    $msg[]    = "Place buyorder for size " . $size . ' and price ' . $buyPrice;
                    $this->placeBuyOrder($size, $buyPrice);
                } else {
                    if ($numOrdersLeftToPlace < 1) {
                        $msg[] = sprintf("Num orders has been reached: Allowed: %d, placed %d", (int)$this->config['max_orders'], (int) $numOpenOrders);
                    }
                }
                $msg[] = "=== DONE " . date('Y-m-d H:i:s') . " ===";
            }
        }

        $this->msg = $msg;
    }
}