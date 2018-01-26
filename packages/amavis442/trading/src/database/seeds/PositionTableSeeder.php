<?php

namespace Amavis442\Trading\Database\Seeder;

use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Order;

class PositionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orders = Order::all();

        foreach ($orders as $order) {
            $position = Position::create([
                'pair' => 'BTC-EUR',
                'size' => $order->size,
                'amount' => $order->amount,
                'open' => $order->amount,
                'status' => 'open',
                'order_id' => $order->order_id,
            ]);

            $order->position_id = $position->id;
            $order->save();
        }
    }
}
