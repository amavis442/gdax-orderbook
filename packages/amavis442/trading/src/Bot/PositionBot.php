<?php
/**
 * Created by PhpStorm.
 * User: patrickteunissen
 * Date: 09-01-18
 * Time: 10:41
 */

namespace Amavis442\Trading\Bot;

use Amavis442\Trading\Contracts\BotInterface;
use Amavis442\Trading\Contracts\GdaxServiceInterface;
use Amavis442\Trading\Contracts\OrderServiceInterface;
use Amavis442\Trading\Contracts\PositionServiceInterface;

class PositionBot implements BotInterface
{
    protected $container;
    protected $config;
    protected $orderService;
    protected $gdax;
    protected $positionService;
    protected $stoplossRule;
    protected $msg = [];


    public function __construct(GdaxServiceInterface $gdax, OrderServiceInterface $orderService, PositionServiceInterface $positionService)
    {
        $this->gdax            = $gdax;
        $this->orderService    = $orderService;
        $this->positionService = $positionService;
    }

    public function setStopLossService($stoplossRule)
    {
        $this->stoplossRule = $stoplossRule;
    }


    public function getMessage(): array
    {
        return $this->msg;
    }

    /**
     * Check if we have added orders manually and add them to the database.
     */
    public function actualize()
    {
        $orders = $this->gdax->getOpenOrders();
        if (count($orders)) {
            $this->orderService->fixUnknownOrdersFromGdax($orders);
        }
    }

    /**
     * Update the open buys
     */
    public function updateBuyOrderStatusAndCreatePosition()
    {
        $orders = $this->orderService->getOpenBuyOrders();

        if (count($orders)) {
            foreach ($orders as $order) {
                /** @var \GDAX\Types\Response\Authenticated\Order $gdaxOrder */
                $gdaxOrder = $this->gdax->getOrder($order['order_id']);
                // Mocken

                $position_id = 0;
                $status      = $gdaxOrder->getStatus();

                if ($status) {
                    if ($status == 'done') {
                        $position_id = $this->positionService->open($gdaxOrder->getId(), $gdaxOrder->getSize(), $gdaxOrder->getPrice());
                    }

                    $this->orderService->updateOrderStatus($order['id'], $gdaxOrder->getStatus(), $position_id);

                } else {
                    $this->orderService->updateOrderStatus($order['id'], $gdaxOrder->getMessage(), $position_id);
                }
            }
        }
    }

    /**
     * Update the open Sells
     */
    public function actualizeSellOrders()
    {
        $orders = $this->orderService->getOpenSellOrders();

        if (is_array($orders)) {
            foreach ($orders as $order) {
                $gdaxOrder = $this->gdax->getOrder($order['order_id']);
                $status    = $gdaxOrder->getStatus();

                if ($status) {
                    $this->orderService->updateOrderStatus($order['id'], $gdaxOrder->getStatus());
                } else {
                    $this->orderService->updateOrderStatus($order['id'], $gdaxOrder->getMessage());
                }
            }
        }
    }

    public function actualizePositions()
    {
        $positions = $this->positionService->getOpen();
        if (is_array($positions)) {
            foreach ($positions as $position) {
                $position_id = $position['id'];
                $order       = $this->orderService->fetchPosition($position_id, 'sell', 'done');

                if ($order) {
                    $this->positionService->close($position_id);
                }
            }
        }
    }

    /**
     * Checks the open buys and if they are filled then place a buy order for the same size but higher price
     */
    protected function watchPositions(float $currentPrice): array
    {
        $positions = $this->positionService->getOpen();

        if (is_array($positions)) {
            $this->msg[] = $this->timestamp . ' .... <info>watchPositions</info>';
            foreach ($positions as $position) {
                $price       = $position['amount'];
                $size        = $position['size'];
                $position_id = $position['id'];
                $order_id    = $position['order_id']; // Buy order_id

                $sellMe    = $this->stoplossRule->trailingStop($position_id, $currentPrice, $price, $this->config['stoploss'], $this->config['takeprofit']);
                $this->msg = array_merge($this->msg, $this->stoplossRule->getMessage());

                $placeOrder = true;
                if ($sellMe) {
                    $this->msg[]       = $this->timestamp . ' .... <info>Sell trigger</info>';
                    $existingSellOrder = $this->orderService->getOpenPosition($position_id);

                    if ($existingSellOrder) {
                        $placeOrder = false;
                        $this->logger->debug('Position ' . $position_id . ' has an open sell order. ');
                    }

                    if ($placeOrder) {
                        $sellPrice = number_format($currentPrice + 0.01, 2, '.', '');

                        $order = $this->gdaxService->placeLimitSellOrderFor1Minute($size, $sellPrice);

                        if ($order->getMessage()) {
                            $status = $order->getMessage();
                        } else {
                            $status = $order->getStatus();
                        }
                        $this->logger->info('Place sell order status ' . $status . ' for position ' . $position_id);


                        if ($status == 'open' || $status == 'pending') {
                            $this->orderService->sell($order->getId(), $size, $sellPrice, $position_id, 0);

                            $this->logger->info('Place sell order ' . $order->getId() . ' for position ' . $position_id);

                            $this->msg[] = $this->timestamp . ' .... <info>Place sell order ' . $order->getId() . ' for position ' . $position_id . '</info>';
                        }
                    }
                }
            }
        }
    }


    protected function init()
    {
        $this->orderService    = $this->container->get('bot.service.order');
        $this->gdaxService     = $this->container->get('bot.service.gdax');
        $this->positionService = $this->container->get('bot.service.position');
        $this->stoplossRule    = $this->container->get('bot.rule.stoploss');
    }


    public function run()
    {
        $this->init();

        $msg = [];
        // Get Account
        //$account = $this->gdaxService->getAccount('EUR');


        $msg[] = "=== RUN [" . \Carbon\Carbon::now('Europe/Amsterdam')->format('Y-m-d H:i:s') . "] ===";


        //Cleanup
        $this->orderService->garbageCollection();

        $this->updateBuyOrderStatusAndCreatePosition();
        $this->actualizeSellOrders();
        $this->actualizePositions();

        $botactive = ($this->config['botactive'] == 1 ? true : false);
        if (!$botactive) {
            $msg[] = "Bot is not active at the moment";
        } else {
            $currentPrice = $this->gdaxService->getCurrentPrice();

            $msg[] = "** Update positions";

            $msgWatch = $this->watchPositions($currentPrice);
            $msg      = array_merge($msg, $msgWatch);

            $msg[] = "=== DONE " . date('Y-m-d H:i:s') . " ===";
        }

        $this->actualizeSellOrders();
        $this->actualizePositions();

        $this->actualize();

        $this->msg = $msg;

    }
}