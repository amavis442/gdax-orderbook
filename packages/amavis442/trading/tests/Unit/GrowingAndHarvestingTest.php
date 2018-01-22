<?php
declare(strict_types=1);

namespace Amavis442\Trading\Tests\Unit;

use Amavis442\Trading\Strategies\GrowingAndHarvesting;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Amavis442\Trading\Models\Position;



final class GrowingAndHarvestingTest extends TestCase
{
     public function test1SlotOpenNoOpenPositionBuyBtc()
    {
        $config = new Collection([
            'coin' => 0.005000,
            'fund' => 30.00,
            'currentprice' => 10450.00,
            'size' => 0.001
        ]);

        $position = new Position();

        $strat = new GrowingAndHarvesting();

        $result = $strat->advise($config, $position);

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
        $config = new Collection([
            'coin' => 0.005000,
            'fund' => 0.00,
            'currentprice' => 10400.00,
            'size' => 0.001
        ]);

        $position = new Position();

        $strat = new GrowingAndHarvesting();

        $result = $strat->advise($config, $position);

        $this->assertEquals('sell', $result->get('side'));
        $this->assertEquals(0.001, $result->get('size'));
        $this->assertEquals(10400.00, $result->get('price'));
    }

    public function testNoSlotOpenAnd1PositionOrderCancelledSellBtc()
    {
        $config = new Collection([
            'coin' => 0.005000,
            'eur' => 0.00,
            'currentprice' => 10450.00,
            'size' => 0.001
        ]);

        $position = new Position();
        $position->id = 1;
        $position->amount = 10400.00;
        $position->open = 10400.00;
        $position->size = 0.01;
        $position->status = 'open';

        $strat = new GrowingAndHarvesting();
        $result = $strat->advise($config, $position);

        $this->assertEquals('sell', $result->get('side'));
        $this->assertEquals($position->size, $result->get('size'));
        $this->assertEquals(10450.00, $result->get('price'));
    }
}


