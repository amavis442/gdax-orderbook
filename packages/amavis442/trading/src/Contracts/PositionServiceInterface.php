<?php
namespace Amavis442\Trading\Contracts;
use Illuminate\Support\Collection;
use Amavis442\Trading\Models\Position;

/**
 * Interface GdaxServiceInterface
 *
 * @package Amavis442\Trading\Contracts
 */
interface PositionServiceInterface
{
    public function purgeDatabase();
    
    
    public function delete(int $id);

   

    /**
     * Insert an order into the database
     *
     * @param string $side
     * @param string $order_id
     * @param string $size
     * @param string $amount
     * @param string $status
     * @param int    $parent_id
     *
     * @return int
     */
    public function open(string $order_id, float $size, float $amount): int;

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function pending(int $id);

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function close(int $id, float $amount);
    
    /**
     * Fetch all orders that have given status
     *
     * @param string $status
     *
     * @return array
     */
    public function fetchAll(string $status = 'open'): Collection;


    /**
     * @param int $id
     *
     * @return \stdClass
     */
    public function fetch(int $id): Position;

    /**
     * Fetch order by order id from coinbase (has the form of aaaaa-aaaa-aaaa-aaaaa)
     *
     * @param string $order_id
     *
     * @return \stdClass
     */
    public function fetchByOrderId(string $order_id): Position;

    /**
     * @return int
     */
    public function getNumOpen(): int;


    /**
     * Get the open sell orders
     *
     * @return array
     */
    public function getOpen(): Collection;
}
