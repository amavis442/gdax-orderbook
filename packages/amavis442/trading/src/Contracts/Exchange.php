<?php

namespace Amavis442\Trading\Contracts;

/**
 * Interface Exchange
 *
 * @package Amavis442\Trading\Contracts
 */
interface Exchange
{

    /**
     * Which crypto coin (BTC,LTC,ETH)
     *
     * @param string $cryptoCoin
     */
    public function usePair(string $pair);


    public function getClient(): \GDAX\Clients\AuthenticatedClient;

    /**
     * Returns orderbook level 2
     *
     * @return \GDAX\Types\Response\Market\ProductOrderBook
     */
    public function getOrderbook(): \GDAX\Types\Response\Market\ProductOrderBook;

    /**
     * Get trades from starting from date
     *
     * @param string|null $date
     *
     * @return array
     */
    public function getTrades(string $date = null): array;

    /**
     * Get an order by order_id which has a format like aaaaaa-aaaa-aaaa-aaaaa
     *
     * @param string $order_id
     *
     * @return \GDAX\Types\Response\Authenticated\Order
     */
    public function getOrder(string $order_id): \GDAX\Types\Response\Authenticated\Order;

    /**
     * @param string $order_id
     * @param string $pair
     *
     * @return \GDAX\Types\Response\Authenticated\Fill
     */
    public function getFilledOrder(string $order_id, string $pair = 'BTC-EUR'): \GDAX\Types\Response\Authenticated\Fill;

    /**
     * Get the productid vb BTC-EUR
     *
     * @return string
     */
    public function getProductId(): string;

    /**
     * Get the orders with status open or pending
     *
     * @return array
     */
    public function getOpenOrders(): array;

    /**
     * Get the last ask price
     *
     * @return float
     */
    public function getCurrentPrice(): float;

    /**
     * Cancel a open/pending order
     *
     * @param string $order_id
     *
     * @return \GDAX\Types\Response\RawData
     */
    public function cancelOrder(string $order_id): \GDAX\Types\Response\RawData;

    /**
     * Place a buy order of a certain size and the limit price
     *
     * @param float $size
     * @param float $price
     *
     * @return \GDAX\Types\Response\Authenticated\Order
     */
    public function placeLimitBuyOrder(float $size, float $price): \GDAX\Types\Response\Authenticated\Order;

    /**
     * @param string $pair
     * @param string $side
     * @param float  $size
     * @param float  $price
     * @param string $ordertype
     * @param string $cancelafter
     * @param float  $stopprice
     * @param float  $stoplimit
     * @param bool   $fake
     * @return \GDAX\Types\Response\Authenticated\Order
     */
    public function placeOrder(
        string $pair = 'BTC-EUR',
        string $side = 'buy',
        float $size,
        float $price,
        string $ordertype = 'limit',
        string $cancelafter = 'minute',
        float $stopprice = 0.0,
        float $stoplimit = 0.0,
        bool $fake = false
    ): \GDAX\Types\Response\Authenticated\Order;

    /**
     * Place a sell order of a certain size and the limit price
     *
     * @param float $size
     * @param float $price
     *
     * @return \GDAX\Types\Response\Authenticated\Order
     */
    public function placeLimitSellOrder(float $size, float $price): \GDAX\Types\Response\Authenticated\Order;

    /**
     * Get the accounts (balance etc)
     */
    public function getAccounts(): array;

    public function getAccount(string $currency): \GDAX\Types\Response\Authenticated\Account;


    /**
     * Get the fills for a certain product_id (vb. BTC-EUR)
     *
     * @return array
     */
    public function getFills(): array;

    /**
     * Report balance, current price and the value in euro's
     *
     * @param string $product
     *
     * @return array
     */
    public function getAccountReport(string $product): array;
}
