<?php
declare(strict_types=1);

namespace Amavis442\Trading\Tests\Unit;

use Illuminate\Support\Collection;
use Tests\TestCase;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Indicators\Stoploss;


final class StopLosIndictorTest extends TestCase
{
    public function testStopLossTriggerPriceGoesUpAndDownAboveTreshold()
    {
        $currentPrice = 12000.00;
        
        $position = new Position();
        $position->id = 1;
        $position->pair = 'BTC-EUR';
        $position->order_id = 'test';
        $position->size = '0.0001';
        $position->amount = $currentPrice;
        $position->open = $currentPrice;
        $position->position = 'open';
        $position->trailingstop = 30.00;

        $st = new Stoploss();
        
        // stoploss = currentprice - position->trailingstop

        $config = new Collection();
        $config->put('currentprice',$currentPrice);
        $config->put('position',$position);

        // Price is new so HOLD
        $result = $st->check($config);
        $this->assertTrue($result === 0);
        
        // Price goes up a litle it, stop is now 12009 - 30 = 11981
        $config->put('currentprice',12009.00);
        $result = $st->check($config);
        $this->assertTrue($result === 0);

        // Price goes up a litle it
        //trailing stop is now 12000
        $config->put('currentprice',12030.00);
        $result = $st->check($config);
        $this->assertTrue($result === 0);
        
        // Price is under last stoploss of 12000 so sell
        $config->put('currentprice',11989.00);
        $result = $st->check($config);
        $this->assertTrue($result === -1);
    }
}


