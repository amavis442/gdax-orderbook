<?php
declare(strict_types=1);

namespace Amavis442\Trading\Services;

use Illuminate\Support\Collection;
use Amavis442\Trading\Models\Position;

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
    public function open(string $order_id, float $size, float $amount): int
    {
        $position = Position::create(['order_id'  => $order_id,
                                    'size'        => $size,
                                    'amount'      => $amount,
                                    'open'        => $amount,
                                    'position'    => 'open',
                                    ]);
        
   
        return $position->id;
    }

    public function pending(int $id)
    {
        DB::table('positions')->where('id', $id)->update(['position' => 'pending']);
    }

    public function close(int $id, float $amount)
    {
        $position = Position::findOrFail($id);
        $position->close = $amount;
        $position->position = 'closed';
        $position->save();
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
        $result = DB::table('positions')->select(DB::raw('count(*) total'))->where('position', 'open')->first();

        return isset($result->total) ? $result->total : 0;
    }

   
    /**
     * Get the open sell orders
     *
     * @return array
     */
    public function getOpen(): array
    {
        $result = DB::select("SELECT * FROM positions WHERE position = 'open'");

        return Transform::toArray($result);
    }

    public function getClosed(): array
    {
        $result = DB::select("SELECT * FROM positions WHERE position = 'closed'");

        return Transform::toArray($result);
    }
}
