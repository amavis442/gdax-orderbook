<?php

namespace Amavis442\Trading\Database\Seeder;

use Illuminate\Database\Seeder;
use Amavis442\Trading\Models\Setting;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'pair' => 'BTC-EUR',
            'trailingstop' => 30.00,
            'max_orders' => 1,
            'tradebottomlimit' => 8500,
            'tradetoplimit' => 9000,
            'minimal_order_size' => 0.001,
            'sellstradle' => 0.10,
            'buystradle' => 0.10,
            'botactive' => 0
        ]);

        Setting::create([
            'pair' => 'ETH-EUR',
            'trailingstop' => 30.00,
            'max_orders' => 1,
            'tradebottomlimit' => 800,
            'tradetoplimit' => 820,
            'minimal_order_size' => 0.01,
            'sellstradle' => 0.07,
            'buystradle' => 0.03,
            'botactive' => 0
        ]);

        Setting::create([
            'pair' => 'LTC-EUR',
            'trailingstop' => 30.00,
            'max_orders' => 1,
            'tradebottomlimit' => 120,
            'tradetoplimit' => 130,
            'minimal_order_size' => 0.01,
            'sellstradle' => 0.07,
            'buystradle' => 0.03,
            'botactive' => 0
        ]);
    }
}
