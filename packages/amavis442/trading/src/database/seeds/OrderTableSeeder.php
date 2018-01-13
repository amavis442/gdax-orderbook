<?php

namespace Amavis442\Trading\Database\Seeder;

use Illuminate\Database\Seeder;
use Amavis442\Trading\Models\Order;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            Order::create([
                                 'pair'     => 'BTC-EUR',
                                 'side'     => 'buy',
                                 'size'     => '0.001',
                                 'amount'   => $faker->numberBetween(12000, 12500),
                                 'status'   => 'open',
                                 'order_id' => $faker->phoneNumber, // Order id format is like 191ca000-ac0a-427f-8c34-65d6e7c6b7be
                             ]);
        }

    }
}
