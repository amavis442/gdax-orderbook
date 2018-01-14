<?php
declare(strict_types=1);

namespace Amavis442\Trading\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Setting;
use Amavis442\Trading\Triggers\Stoploss;


final class StopLosTriggerTest extends TestCase
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

        // Price is new so HOLD
        $result = $st->signal($currentPrice, $position);
        $this->assertTrue($result === 0);
        
        // Price goes up a litle it, stop is now 12009 - 30 = 11981
        $currentPrice = 12009.00;
        $result = $st->signal($currentPrice, $position);
        $this->assertTrue($result === 0);

        // Price goes up a litle it
        $currentPrice = 12030.00; //trailing stop is now 12000
        $result = $st->signal($currentPrice, $position);
        $this->assertTrue($result === 0);
        
        // Price is under last stoploss of 12000 so sell
        $currentPrice = 11989.00;
        $result = $st->signal($currentPrice, $position);
        $this->assertTrue($result === -1);
    }
}


