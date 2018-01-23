<?php
declare(strict_types=1);

namespace Amavis442\Trading\Tests\Unit;

use Amavis442\Trading\Strategies\GrowingAndHarvesting;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Setting;
use Illuminate\Support\Facades\Cache;

final class GrowingAndHarvestingTest extends TestCase
{
     public function test1SlotOpenNoOpenPositionBuyBtc()
    {
        $settings = new Setting();
        $settings->bottom = 0.0;
        $settings->top = 20000.0;
        $settings->size = 0.001;

        Cache::put('bot::settings', $settings->toJson(), 1);
        Cache::put('bot::pair', 'BTC-EUR', 1);
        Cache::put('bot::stradle', 0.01, 1);
        Cache::put('config::fund', 30.00, 1);
        Cache::put('config::coin', 0.005000, 1);
        Cache::put('gdax::BTC-EUR::currentprice', 10450.00, 2);

        $position = new Position();
        $position->open = 10460.00;

        $strat = new GrowingAndHarvesting();

        $result = $strat->advise($position);

        $price = 10450.00;
        $spread = 0.01;
        $buyprice = $price - $spread;
        $funds = 30.00;
        $size = (string)($funds / $buyprice);
        $check = substr($size,0, strpos($size,'.') + 9);

        $this->assertEquals('buy', $result->get('side'));
        $this->assertEquals($check, $result->get('size'));
        $this->assertEquals(10449.99, $result->get('price'));
    }

    public function test1SlotOpenNoPositionSellBtc()
    {
        $settings = new Setting();
        $settings->bottom = 0.0;
        $settings->top = 20000.0;
        $settings->size = 0.001;

        Cache::put('bot::settings', $settings->toJson(), 1);
        Cache::put('bot::pair', 'BTC-EUR', 1);
        Cache::put('bot::stradle', 0.01, 1);
        Cache::put('config::fund', 0.00, 1);
        Cache::put('config::coin', 0.005000, 1);
        Cache::put('gdax::BTC-EUR::currentprice', 10400.00, 2);



        $position = new Position();

        $strat = new GrowingAndHarvesting();

        $result = $strat->advise($position);

        $this->assertEquals('sell', $result->get('side'));
        $this->assertEquals(0.001, $result->get('size'));
        $this->assertEquals(10400.00, $result->get('price'));
    }

    public function testNoSlotOpenAnd1PositionOrderCancelledSellBtc()
    {

        $settings = new Setting();
        $settings->bottom = 0.0;
        $settings->top = 20000.0;
        $settings->size = 0.001;

        Cache::put('bot::settings', $settings->toJson(), 1);
        Cache::put('bot::pair', 'BTC-EUR', 1);
        Cache::put('bot::stradle', 0.01, 1);
        Cache::put('config::fund', 0.00, 1);
        Cache::put('config::coin', 0.005000, 1);
        Cache::put('gdax::BTC-EUR::currentprice', 10450.00, 2);


        $position = new Position();
        $position->id = 1;
        $position->amount = 10400.00;
        $position->open = 10400.00;
        $position->size = 0.01;
        $position->status = 'open';

        $strat = new GrowingAndHarvesting();
        $result = $strat->advise($position);

        $this->assertEquals('sell', $result->get('side'));
        $this->assertEquals($position->size, $result->get('size'));
        $this->assertEquals(10450.00, $result->get('price'));
    }
}


