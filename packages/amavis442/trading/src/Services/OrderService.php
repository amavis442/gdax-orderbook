<?php
declare(strict_types=1);

namespace Amavis442\Trading\Services;

use Amavis442\Trading\Contracts\OrderServiceInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Amavis442\Trading\Util\Transform;

/**
 * Class OrderService
 *
 * @package Amavis442\Trading\Services
 */
class OrderService implements OrderServiceInterface
{

    /**
     *
     */
    public function purgeDatabase()
    {
        DB::table('orders')->delete();
    }

    /**
     * @param int $id
     *
     * @return mixed|void
     */
    public function deleteOrder(int $id)
    {
        DB::table('orders')->delete($id);
    }

    /**
     * @param int    $id
     * @param string $side
     *
     * @return mixed|void
     */
    public function updateOrder(int $id, string $side)
    {
        DB::table('orders')->where('id', $id)->update(['side' => $side]);
    }

    /**
     * @param int    $id
     * @param string $status
     *
     * @return mixed|void
     */
    public function updateOrderStatus(int $id, string $status, int $position_id = 0)
    {
        if ($position_id == 0) {
            $data = ['status' => $status];
        } else {
            $data = ['status' => $status,'position_id' => $position_id];
        }

        DB::table('orders')->where('id', $id)->update($data);
    }

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
    public function insertOrder(string $side, string $order_id, float $size, float $amount, string $status = 'pending', int $parent_id = 0, int $position_id = 0, string $strategy = 'TrendsLines'): int
    {
        $id = DB::table('orders')->insertGetId([
                                                   'side'        => $side,
                                                   'order_id'    => $order_id,
                                                   'size'        => $size,
                                                   'amount'      => $amount,
                                                   'position_id' => $position_id,
                                                   'status'      => $status,
                                                   'parent_id'   => $parent_id,
                                                   'created_at'  => date('Y-m-d H:i:s')
                                               ]);

        return $id;
    }

    public function buy(string $order_id, float $size, float $amount,int $position_id = 0, int $parent_id = 0): int
    {
        $id = DB::table('orders')->insertGetId([
                                                   'side'        => 'buy',
                                                   'order_id'    => $order_id,
                                                   'size'        => $size,
                                                   'amount'      => $amount,
                                                   'position_id' => $position_id,
                                                   'status'      => 'pending',
                                                   'parent_id'   => $parent_id,
                                                   'created_at'  => date('Y-m-d H:i:s')
                                               ]);

        return $id;
    }

    public function sell(string $order_id, float $size, float $amount,int $position_id = 0, int $parent_id = 0) :int
    {
        $id = DB::table('orders')->insertGetId([
                                                   'side'        => 'sell',
                                                   'order_id'    => $order_id,
                                                   'size'        => $size,
                                                   'amount'      => $amount,
                                                   'position_id' => $position_id,
                                                   'status'      => 'pending',
                                                   'parent_id'   => $parent_id,
                                                   'created_at'  => date('Y-m-d H:i:s')
                                               ]);

        return $id;
    }


    /**
     * Get rid of the failures.
     */
    public function garbageCollection()
    {
        DB::table('orders')->where('order_id', '')->where('status', '<>', 'deleted')->update(['status' => 'deleted']);
    }


    /**
     * Get the open buy orders
     *
     * @return array
     */
    public function getPendingBuyOrders(): array
    {
        $result = DB::table('orders')->select('*')->where('side', 'buy')->where('status', 'IN', ['pending', 'open'])->get();

        return Transform::toArray($result);
    }

    /**
     * Fetch all orders that have given status
     *
     * @param string $status
     *
     * @return array
     */
    public function fetchAllOrders(string $status = 'pending'): array
    {
        $result = DB::table('orders')->select('*')->where('status', $status)->get();

        return Transform::toArray($result);
    }

    /**
     * @param int $id
     *
     * @return \stdClass
     */
    public function fetchOrder(int $id): ?\stdClass
    {
        $result = DB::table('orders')->select('*')->where('id', $id)->first();

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    public function getOpenPosition(int $id): ?\stdClass
    {
        $result = DB::table('orders')->select('*')->where('position_id', $id)->where('side', 'sell')->whereIn('status', ['open','pending'])->first();

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param int $id
     *
     * @return \stdClass
     */
    public function fetchPosition(int $position_id, string $side, string $status = 'done'): ?\stdClass
    {
        $result = DB::table('orders')->select('*')->where('position_id', $position_id)->where('side', $side)->where('status',$status)->first();

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }


    public function fetchOrderByParentId(int $parent_id, string $status = 'open'): ?\stdClass
    {
        $result = DB::table('orders')->select('*')->where('parent_id', $parent_id)->where('status',$status)->first();

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }



    /**
     * Fetch order by order id from coinbase (has the form of aaaaa-aaaa-aaaa-aaaaa)
     *
     * @param string $order_id
     *
     * @return \stdClass
     */
    public function fetchOrderByOrderId(string $order_id): ?\stdClass
    {
        $result = DB::table('orders')->select('*')->where('order_id', $order_id)->first();
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    public function getOpenSellOrderByOrderId(string $order_id): ?\stdClass
    {
        $result = DB::table('orders')->select('*')->where('order_id', $order_id)->whereIn('status',['open','pending'])->orderBy('id','desc')->first();

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }



    /**
     * @return int
     */
    public function getNumOpenOrders(): int
    {
        $result = DB::table('orders')->select(DB::raw('count(*) total'))->where('status', 'open')->orWhere('status', 'pending')->first();

        return isset($result->total) ? $result->total : 0;
    }

    /**
     * @return float
     */
    public function getLowestSellPrice(): ?float
    {
        $result = DB::select("SELECT min(amount) minprice FROM orders WHERE side='sell' AND status = 'open' OR status = 'pending'");
        $row    = $result[0];

        return isset($row->minprice) ? $row->minprice : null;
    }

    public function getTopOpenBuyOrder(): ?\stdClass {
        $result = DB::select("SELECT * from orders WHERE side='buy' AND status = 'open' OR status = 'pending' AND amount = (SELECT MAX(amount) maxamount from orders WHERE side='buy' AND status = 'open' OR status = 'pending') limit 1");
        if ($result) {
            return (object) $result[0];
        }
        return null ;
    }

    public function getBottomOpenBuyOrder(): ?\stdClass {
        $result = DB::select("SELECT * from orders WHERE side='buy' AND status = 'open' OR status = 'pending' AND amount = (SELECT MIN(amount) maxamount from orders WHERE side='buy' AND status = 'open' OR status = 'pending') limit 1");
        if ($result) {
            return (object) $result[0];
        }
        return null ;
    }

    public function getTopOpenSellOrder(): ?\stdClass {
        $result = DB::select("SELECT * from orders WHERE side='sell' AND status = 'open' OR status = 'pending' AND amount = (SELECT MAX(amount) maxamount from orders WHERE side='sell' AND status = 'open' OR status = 'pending') limit 1");
        if ($result) {
            return (object) $result[0];
        }
        return null ;
    }

    public function getBottomOpenSellOrder(): ?\stdClass {
        $result = DB::select("SELECT * from orders WHERE side='sell' AND status = 'open' OR status = 'pending' AND amount = (SELECT MIN(amount) maxamount from orders WHERE side='sell' AND status = 'open' OR status = 'pending') limit 1");
        if ($result) {
            return (object) $result[0];
        }
        return null ;
    }

    /**
     *
     * @param string $side
     * @param string $status
     *
     * @return array
     */
    public function getOrdersBySide(string $side, string $status = 'pending'): array
    {
        $result = DB::table('order')->where('side', $side)->where('status', $status)->get();

        return Transform::toArray($result);
    }

    /**
     * Get the open sell orders
     *
     * @return array
     */
    public function getOpenSellOrders(): array
    {
        $result = DB::select("SELECT * FROM orders WHERE side = 'sell' AND (status = 'pending' OR status = 'open')");

        return Transform::toArray($result);
    }

    /**
     * Get the open buy orders
     *
     * @return array
     */
    public function getOpenBuyOrders(): array
    {
        $result = DB::select("SELECT * FROM orders WHERE side = 'buy' AND (status = 'pending' OR status = 'open')");

        return Transform::toArray($result);
    }


    public function getNumOpenBuyOrders(): int
    {
        $result = DB::table('orders')->select(DB::raw('count(*) total'))->where('side','buy')->where('status', 'open')->orWhere('status', 'pending')->first();

        return isset($result->total) ? $result->total : 0;
    }


    /**
     * Check of we already have a open buy order with that price
     *
     * @param float $price
     *
     * @return bool
     */
    public function buyPriceExists(float $price): bool
    {
        $result = DB::select("SELECT * FROM orders WHERE side = 'buy' AND (status = 'pending' OR status = 'open') AND amount = $price");
        foreach ($result as $row) {
            if ($row->amount) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     */
    public function listRowsFromDatabase()
    {
        $currentPendingOrders = $this->fetchAllOrders();
        foreach ($currentPendingOrders as $row) {
            printf("%s| %s| %s| %s\n", $row->created_at, $row->side, $row->amount, $row->order_id);
        }
    }

    /**
     * Sometimes the price fluctuates to much and can result in a rejected order.
     *
     */
    public function fixRejectedSells()
    {
        $result = DB::select("SELECT * FROM orders WHERE status = 'rejected' AND side='sell' AND parent_id > 0");

        foreach ($result as $row) {
            DB::table('orders')->where('id', $row->parent_id)->update(['status' => 'open']);
            DB::table('orders')->where('id', $row->id)->update(['status' => 'fixed']);
        }
    }

    /**
     * Orders can also be inserted by hand
     *
     * @param array $orders
     *
     * @return mixed|void
     */
    public function fixUnknownOrdersFromGdax(array $orders = null)
    {
        if (is_array($orders)) {
            foreach ($orders as $order) {
                $order_id = $order->getId();
                $row      = $this->fetchOrderByOrderId($order_id);
                if (!$row) {
                    $this->insertOrder($order->getSide(), $order->getId(), $order->getSize(), $order->getPrice(), 'Manual');
                } else {
                    if ($row->status != 'done') {
                        $this->updateOrderStatus($row->id, $order->getStatus());
                    }
                }
            }
        }
    }

    /**
     * @param string|null $date
     *
     * @return array
     */
    public function getProfits(string $date = null): array
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }
        $date .= ' 00:00:00';

        $result = DB::select("SELECT b.created_at buydate,b.side as buyside,b.size buysize,b.amount buyamount,s.created_at selldate,s.side sellside,s.size sellsize,s.amount sellamount, (s.amount - b.amount) * s.size as profit FROM orders s, " .
                             "(SELECT * FROM orders WHERE side='buy' AND `status`='done') b " .
                             " WHERE s.side='sell' AND s.status='done' AND b.id = s.parent_id AND b.created_at >= '$date'");

        return Transform::toArray($result);
    }

}