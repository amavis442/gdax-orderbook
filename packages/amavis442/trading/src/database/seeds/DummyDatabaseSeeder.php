<?php

namespace Amavis442\Trading\Database\Seeder;

use Illuminate\Database\Seeder;

class DummyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(OrderTableSeeder::class);
        $this->call(PositionTableSeeder::class);
    }
}
