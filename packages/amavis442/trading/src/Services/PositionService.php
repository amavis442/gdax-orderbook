<?php
declare(strict_types=1);

namespace Amavis442\Trading\Services;

use Illuminate\Support\Collection;
use Amavis442\Trading\Models\Position;
use Illuminate\Support\Facades\DB;

/**
 * Class OrderService
 *
 * @package Amavis442\Trading\Services
 */
class PositionService
{
    /**
     * Purges the table
     */
    public function purgeDatabase()
    {
        DB::table('positions')->delete();
    }

    /**
     * @param int $id
     */
    public function delete(int $id)
    {
        $position = Position::findOrFail($id);
        $position->delete();
    }


    /**
     * @param string $pair
     * @param string $order_id
     * @param float  $size
     * @param float  $price
     *
     * @return \Amavis442\Trading\Models\Position
     */
    public function open(string $pair, string $order_id, float $size, float $price): Position
    {
        $position = Position::create([
            'pair'     => $pair,
            'order_id' => $order_id,
            'size'     => $size,
            'amount'   => $price,
            'open'     => $price,
            'status' => 'open',
        ]);


        return $position;
    }

    /**
     * Sets status to pending
     *
     * @param int $id
     */
    public function pending(int $id)
    {
        $position = Position::findOrFail($id);
        $position->status = 'pending';
        $position->save();
    }

    /**
     * @param int   $id
     * @param float $size
     * @param float $price
     *
     * @return \Amavis442\Trading\Models\Position
     */
    public function close(int $id, float $size, float $price): Position
    {
        $position = Position::findOrFail($id);
        $position->close = $price;
        $position->size = $size;
        $position->status = 'closed';
        $position->save();

        return $position;
    }

    /**
     * Fetch all orders that have given status
     *
     * @param string $status
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetchAll(string $status = 'open'): Collection
    {
        return Position::whereStatus($status)->get();
    }

    /**
     * @param int $id
     *
     * @return \Amavis442\Trading\Models\Position
     */
    public function fetch(int $id): Position
    {
        return Position::findOrFail($id);
    }

    /**
     * Fetch order by order id from coinbase (has the form of aaaaa-aaaa-aaaa-aaaaa)
     *
     * @param string $order_id
     *
     * @return \Amavis442\Trading\Models\Position
     */
    public function fetchByOrderId(string $order_id): Position
    {
        $result = Position::whereOrderId($order_id)->first();
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param string $pair
     *
     * @return int
     */
    public function getNumOpen(string $pair = 'BTC-EUR'): int
    {
        $result = Position::selectRaw('count(*) total')
                          ->wherePair($pair)
                          ->where('status', 'open')
                          ->first();

        return isset($result->total) ? $result->total : 0;
    }


    /**
     * Get the open sell orders
     *
     * @param string $pair
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOpen(string $pair = 'BTC-EUR'): Collection
    {
        $result = Position::wherePair($pair)
                          ->whereStatus('open')
                          ->get();

        return $result;
    }

    /**
     * @param string $pair
     *
     * @return \Illuminate\Support\Collection
     */
    public function getClosed($pair = 'BTC-EUR'): Collection
    {
        $result = Position::wherePair($pair)
                          ->whereStatus('closed')
                          ->get();

        return $result;
    }
}
