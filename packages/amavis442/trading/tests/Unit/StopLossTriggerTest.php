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
 
        $config = new Setting();
        $config->spread  = 10.00;
        $config->sellspread = 20.00;
        $config->stoploss = 3.00;
        $config->takeprofit = 1;
        $config->takeprofittreshold = 10.00;
        $config->max_orders = 1;
        $config->bottom = 10000.00;
        $config->top = 15000.00;
        $config->size = '0.0001';
        $config->lifetime = 90;
        $config->botactive = 1;
        
        $st = new Stoploss();
        
        // stoploss treshold = 12000 * ((100 - $config->stoploss)/ 100) = 11640
        // takeprofit treshold = 12000 * ((100 + $config->takeprofit)/ 100) = 12120
        
        // Price is new so HOLD
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up a litle it
        $currentPrice = 12009.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up a litle it
        $currentPrice = 12011.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up a litle it
        $currentPrice = 11911.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        
        // Price goes up stop should be 12500 * 0.99 = 12375.00 (hold)
        $currentPrice = 12500.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up stop should be 12500 * 0.99 = 12375.00 (hold)
        $currentPrice = 12450.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up stop should be 12600 * 0.99 = 12474.00 (hold)
        $currentPrice = 12600.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up stop should be 12600 * 0.99 = 12474.00 (hold)
        $currentPrice = 12500.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up stop should be 12600 * 0.99 = 12474.00 (hold)
        $currentPrice = 12473.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === -1);
    }
    
    public function testStopLossTriggerPriceGoesUpAndDownUnderStoplossTreshold()
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
 
        $config = new Setting();
        $config->spread  = 10.00;
        $config->sellspread = 20.00;
        $config->stoploss = 3.0;
        $config->takeprofit = 1.00;
        $config->takeprofittreshold = 10.00;
        $config->max_orders = 1;
        $config->bottom = 10000.00;
        $config->top = 15000.00;
        $config->size = '0.0001';
        $config->lifetime = 90;
        $config->botactive = 1;
        
        $st = new Stoploss();
        
        // stoploss treshold = 12000 * ((100 - $config->stoploss)/ 100) = 11640
        // takeprofit treshold = 12000 * ((100 + $config->takeprofit)/ 100) = 12120
        
        // Price is new so HOLD
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up a litle it
        $currentPrice = 12009.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up a litle it
        $currentPrice = 12011.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up a litle it
        $currentPrice = 11911.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === 0);
        
        // Price goes up a litle it
        $currentPrice = 11638.00;
        $result = $st->signal($currentPrice, $position, $config);
        $this->assertTrue($result === -1);
    }
    
}


