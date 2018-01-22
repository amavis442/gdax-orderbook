<?php
declare(strict_types=1);

namespace Amavis442\Trading\Services;

use Amavis442\Trading\Contracts\Exchange;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Amavis442\Trading\Models\Order;

/**
 * Class OrderService
 *
 * @package Amavis442\Trading\Services
 */
class OrderService
{
    protected $exchange;

    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }


    /**
     * Purges the table
     */
    public function purgeDatabase()
    {
        DB::table('orders')->delete();
    }

    /**
     * @param int $id
     */
    public function deleteOrder(int $id)
    {
        $order = Order::findOrFail($id);
        $order->delete($id);
    }

    /**
     * @param int    $id
     * @param string $side
     */
    public function updateOrder(int $id, string $side)
    {
        $order = Order::findOrFail($id);
        $order->side = $side;
        $order->save();
    }

    /**
     * @param int    $id
     * @param string $status
     * @param int    $position_id
     */
    public function updateOrderStatus(int $id, string $status, int $position_id = 0)
    {
        $order = Order::findOrFail($id);

        if ($position_id == 0) {
            $order->position_id = $position_id;
        }
        $order->status = $status;
        $order->save();

    }

    /**
     * Insert an order into the database
     *
     * @param string $pair
     * @param string $side
     * @param string $order_id
     * @param float  $size
     * @param float  $amount
     * @param string $status
     * @param int    $parent_id
     * @param int    $position_id
     * @param string $strategy
     *
     * @return int
     */
    public function insertOrder(
        string $pair,
        string $side,
        string $order_id,
        float $size,
        float $amount,
        string $status = 'pending',
        int $parent_id = 0,
        int $position_id = 0,
        string $strategy = 'TrendsLines'
    ): int {
        $id = DB::table('orders')->insertGetId([
            'pair'        => $pair,
            'side'        => $side,
            'order_id'    => $order_id,
            'size'        => $size,
            'amount'      => $amount,
            'position_id' => $position_id,
            'status'      => $status,
            'parent_id'   => $parent_id,
            'strategy'    => $strategy,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $id;
    }

    /**
     * @param string $pair
     * @param float  $size
     * @param float  $price
     * @param int    $position_id
     * @param int    $parent_id
     *
     * @return int
     */
    public function buy(string $pair, float $size, float $price, int $position_id = 0, int $parent_id = 0): int
    {
        $order = $this->exchange->placeOrder($pair, 'buy', $size, $price);

        if ($order->getId() && ($order->getStatus() == \GDAX\Utilities\GDAXConstants::ORDER_STATUS_PENDING ||
                                $order->getStatus() == \GDAX\Utilities\GDAXConstants::ORDER_STATUS_OPEN)
        ) {

            $id = DB::table('orders')->insertGetId([
                'pair'        => $pair,
                'side'        => 'buy',
                'order_id'    => $order->getId(),
                'size'        => $size,
                'amount'      => $price,
                'position_id' => $position_id,
                'status'      => 'pending',
                'parent_id'   => $parent_id,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } else {
            $reason = $order->getMessage() . $order->getRejectReason() . ' ';
            $this->insertOrder($pair, 'buy', 'rejected', $size, $price, (string)$reason);
            $id = -1;
        }


        return $id;
    }

    /**
     * @param string $pair
     * @param string $order_id
     * @param float  $size
     * @param float  $price
     * @param int    $position_id
     * @param int    $parent_id
     *
     * @return int
     */
    public function sell(
        string $pair,
        string $order_id,
        float $size,
        float $price,
        int $position_id = 0,
        int $parent_id = 0
    ): int {
        $order = $this->exchange->placeOrder($pair, 'sell', $size, $price);

        if ($order->getId() && ($order->getStatus() == \GDAX\Utilities\GDAXConstants::ORDER_STATUS_PENDING ||
                                $order->getStatus() == \GDAX\Utilities\GDAXConstants::ORDER_STATUS_OPEN)
        ) {
            $id = DB::table('orders')->insertGetId([
                'pair'        => $pair,
                'side'        => 'sell',
                'order_id'    => $order_id,
                'size'        => $size,
                'amount'      => $price,
                'position_id' => $position_id,
                'status'      => 'pending',
                'parent_id'   => $parent_id,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } else {
            $reason = $order->getMessage() . $order->getRejectReason() . ' ';
            $this->insertOrder($pair, 'sell', 'rejected', $size, $price, (string)$reason);
            $id = -1;
        }

        return $id;
    }

    /**
     * @param \GDAX\Types\Response\Authenticated\Order $order
     * @param string                                   $strategy
     * @param int                                      $position_id
     *
     * @return int
     */
    public function insert(\GDAX\Types\Response\Authenticated\Order $order, $strategy = '', $position_id = 0)
    {
        if ($order->getId() && ($order->getStatus() == \GDAX\Utilities\GDAXConstants::ORDER_STATUS_PENDING ||
                                $order->getStatus() == \GDAX\Utilities\GDAXConstants::ORDER_STATUS_OPEN)
        ) {
            $id = DB::table('orders')->insertGetId([
                'side'        => $order->getSide(),
                'pair'        => $order->getProductId(),
                'order_id'    => $order->getId(),
                'size'        => $order->getSize(),
                'amount'      => $order->getPrice(),
                'position_id' => $position_id,
                'status'      => 'pending',
                'strategy'    => $strategy,
                'parent_id'   => 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } else {
            $reason = $order->getMessage() . $order->getRejectReason() . ' ';
            $this->insertOrder(
                $order->getProductId(),
                'sell',
                'rejected',
                $order->getSize(),
                $order->getPrice(),
                $reason,
                0,
                0,
                $strategy
            );
            $id = -1;
        }

        return $id;
    }

    public  function updateStatus(Order $order, \GDAX\Types\Response\Authenticated\Order $exchangeOrder)
    {
        $status = $exchangeOrder->getStatus();
        if ($status != null) {
            $order->status = $status;
        } else {
            $order->status = $exchangeOrder->getMessage();
        }
        $order->save();

        return $order;
    }

    /**
     * Get rid of the failures.
     */
    public function garbageCollection()
    {
        DB::table('orders')->where('order_id', '')
          ->where('status', '<>', 'deleted')
          ->update(['status' => 'deleted']);
    }


    /**
     * Get the open buy orders
     *
     * @param string $pair
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPendingBuyOrders(string $pair): Collection
    {
        $result = Order::whereSide('buy')
                       ->whereIn('status', ['pending', 'open'])
                       ->wherePair($pair)->get();

        return $result;
    }

    /**
     * Fetch all orders that have given status
     *
     * @param string $status
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetchAllOrders(string $status = 'pending'): Collection
    {
        $result = Order::whereStatus($status)
                       ->get();

        return $result;
    }

    /**
     * @param int $id
     *
     * @return \Amavis442\Trading\Models\Order|null
     */
    public function fetchOrder(int $id): ?Order
    {
        $result = Order::find($id);

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param int $id
     *
     * @return \Amavis442\Trading\Models\Order|null
     */
    public function getOpenPosition(int $id): ?Order
    {
        $result = Order::wherePositionId($id)
                       ->whereSide('sell')
                       ->whereIn('status', ['open', 'pending'])
                       ->first();

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param int    $position_id
     * @param string $side
     * @param string $status
     *
     * @return \Amavis442\Trading\Models\Order|null
     */
    public function fetchPosition(int $position_id, string $side, string $status = 'done'): ?Order
    {
        $result = Order::wherePositionId($position_id)->whereSide($side)->whereStatus($status)->first();

        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param int    $parent_id
     * @param string $status
     *
     * @return \Amavis442\Trading\Models\Order|null
     */
    public function fetchOrderByParentId(int $parent_id, string $status = 'open'): ?Order
    {
        $result = Order::whereParentId($parent_id)->whereStatus($status)->first();

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
     * @return \Amavis442\Trading\Models\Order|null
     */
    public function fetchOrderByOrderId(string $order_id): ?Order
    {
        $result = Order::whereOrderId($order_id)->first();
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param string $order_id
     *
     * @return \Amavis442\Trading\Models\Order|null
     */
    public function getOpenSellOrderByOrderId(string $order_id): ?Order
    {
        $result = Order::whereOrderId($order_id)
                       ->whereIn('status', ['open', 'pending',])
                       ->orderBy('id', 'desc')->first();

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
    public function getNumOpenOrders(string $pair = 'BTC-EUR'): int
    {
        $result = Order::selectRaw('count(*) total')
                       ->whereIn('status', ['open', 'pending'])
                       ->wherePair($pair)
                       ->first();

        return isset($result->total) ? $result->total : 0;
    }

    /**
     * @return float|null
     */
    public function getLowestSellPrice(): ?float
    {
        $result = DB::select("SELECT min(amount) minprice FROM orders WHERE side='sell'" .
                             " AND status = 'open' OR status = 'pending'");
        $row = $result[0];

        return isset($row->minprice) ? $row->minprice : null;
    }

    /**
     * @return null|\stdClass
     */
    public function getTopOpenBuyOrder(): ?\stdClass
    {
        $result = DB::select("SELECT * from orders WHERE side='buy' AND status = 'open' OR status = 'pending'" .
                             " AND amount = (SELECT MAX(amount) maxamount from orders WHERE side='buy'" .
                             " AND status = 'open' OR status = 'pending') limit 1");
        if ($result) {
            return (object)$result[0];
        }

        return null;
    }

    /**
     * @return null|\stdClass
     */
    public function getBottomOpenBuyOrder(): ?\stdClass
    {
        $result = DB::select("SELECT * from orders WHERE side='buy' AND status = 'open' OR status = 'pending'" .
                             " AND amount = (SELECT MIN(amount) maxamount from orders WHERE side='buy'" .
                             " AND status = 'open' OR status = 'pending') limit 1");
        if ($result) {
            return $result[0];
        }

        return null;
    }

    /**
     * @return null|\stdClass
     */
    public function getTopOpenSellOrder(): ?\stdClass
    {
        $result = DB::select("SELECT * from orders WHERE side='sell' AND status = 'open' OR status = 'pending'" .
                             " AND amount = (SELECT MAX(amount) maxamount from orders WHERE side='sell'" .
                             " AND status = 'open' OR status = 'pending') limit 1");
        if ($result) {
            return (object)$result[0];
        }

        return null;
    }

    /**
     * @return null|\stdClass
     */
    public function getBottomOpenSellOrder(): ?\stdClass
    {
        $result = DB::select("SELECT * from orders WHERE side='sell' AND status = 'open' OR status = 'pending'" .
                             " AND amount = (SELECT MIN(amount) maxamount from orders WHERE side='sell' AND" .
                             " status = 'open' OR status = 'pending') limit 1");
        if ($result) {
            return (object)$result[0];
        }

        return null;
    }

    /**
     * @param string $side
     * @param string $status
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOrdersBySide(string $side, string $status = 'pending'): Collection
    {
        $result = Order::whereSide($side)
                       ->whereStatus($status)
                       ->get();

        return $result;
    }

    /**
     * Get the open sell orders

     * @return \Illuminate\Support\Collection
     */
    public function getOpenSellOrders(): Collection
    {
        $result = Order::whereSide('sell')->where(function ($q) {
            $q->whereIn('status', ['open', 'pending']);
            $q->orWhereNull('status');
        })->get();

        return $result;
    }

    /**
     * Get the open buy orders
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOpenBuyOrders(): Collection
    {
        $result = Order::whereSide('buy')->where(function ($q) {
            $q->whereIn('status', ['open', 'pending']);
            $q->orWhereNull('status');
        })->get();

        return $result;
    }

    /**
     * @return int
     */
    public function getNumOpenBuyOrders(): int
    {
        $result = Order::selectRaw('count(*) total')
                       ->where('side', 'buy')
                       ->whereIn('status', ['open', 'pending'])
                       ->first();

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
        $result = Order::whereSide('buy')
                       ->whereIn('status', ['pending', 'open'])
                       ->whereAmount($price)
                       ->get();

        foreach ($result as $row) {
            if ($row->amount) {
                return true;
            }
        }

        return false;
    }

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
        $result = Order::whereStatus('rejected')
                       ->whereSide('sell')
                       ->where('parent_id', '>', 0);

        foreach ($result as $row) {
            DB::table('orders')->where('id', $row->parent_id)->update(['status' => 'open']);
            DB::table('orders')->where('id', $row->id)->update(['status' => 'fixed']);
        }
    }

    /**
     * Orders can also be inserted by hand
     *
     * @param array|null $orders
     */
    public function fixUnknownOrdersFromGdax(array $orders = null)
    {
        if (is_array($orders)) {
            foreach ($orders as $order) {
                $order_id = $order->getId();
                $row = $this->fetchOrderByOrderId($order_id);
                if (!$row) {
                    $this->insertOrder(
                        $order->getProdctId(),
                        $order->getSide(),
                        $order->getId(),
                        $order->getSize(),
                        $order->getPrice(),
                        'manual');
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

        $result = DB::select('SELECT b.created_at buydate,b.side as buyside,b.size buysize,b.amount buyamount,' .
                             's.created_at selldate,s.side sellside,s.size sellsize,s.amount sellamount,' .
                             '(s.amount - b.amount) * s.size as profit FROM orders s, ' .
                             "(SELECT * FROM orders WHERE side='buy' AND `status`='done') b " .
                             " WHERE s.side='sell' AND s.status='done' AND b.id = s.parent_id " .
                             "AND b.created_at >= '$date'");

        return $result;
    }

}