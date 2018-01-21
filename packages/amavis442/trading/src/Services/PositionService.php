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
     *
     */
    public function purgeDatabase()
    {
        DB::table('positions')->delete();
    }

    /**
     * @param int $id
     *
     * @return mixed|void
     */
    public function delete(int $id)
    {
        DB::table('positions')->delete($id);
    }

    /**
     *
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

    public function pending(int $id)
    {
        DB::table('positions')->where('id', $id)->update(['status' => 'pending']);
    }

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
     * @return array
     */
    public function fetchAll(string $status = 'open'): Collection
    {
        return Position::whereStatus($status)->get();
    }

    /**
     * @param int $id
     *
     * @return \stdClass
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
     * @return \stdClass
     */
    public function fetchByOrderId(string $order_id): Position
    {
        $result = DB::table('positions')->select('*')->where('order_id', $order_id)->first();
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @return int
     */
    public function getNumOpen(): int
    {
        $result = Position::selectRaw('count(*) total')->where('status', 'open')->first();

        return isset($result->total) ? $result->total : 0;
    }


    /**
     * Get the open sell orders
     *
     * @return array
     */
    public function getOpen($pair = 'BTC-EUR'): Collection
    {
        $result = Position::wherePair($pair)->whereStatus('open')->get();

        return $result;
    }

    public function getClosed($pair = 'BTC-EUR'): Collection
    {
        $result = Position::wherePair($pair)->whereStatus('closed')->get();

        return $result;
    }
}
