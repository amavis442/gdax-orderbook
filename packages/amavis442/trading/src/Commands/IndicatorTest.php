<?php

namespace Amavis442\Trading\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Amavis442\Trading\Models\Ticker;

use Amavis442\Trading\Contracts\Indicator;
use Amavis442\Trading\Indicators\AverageDirectionalMovementIndexIndicator;
use Amavis442\Trading\Indicators\OnBalanceVolumeIndicator;
use Amavis442\Trading\Indicators\CommodityChannelIndexIndicator;
use Amavis442\Trading\Indicators\HilbertTransformInstantaneousTrendlineIndicator;
use Amavis442\Trading\Indicators\HilbertTransformTrendVersusCycleModeIndicator;
use Amavis442\Trading\Indicators\MoneyFlowIndexIndicator;
use Amavis442\Trading\Indicators\MovingAverageCrossoverDivergenceIndicator;

/**
 * Description of RunBotCommand
 *
 * @author patrick
 */
class IndicatorTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:indicator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buys and sells position depending on the given strategy.';

    public function toText($r)
    {
        switch ($r) {
            case Indicator::BUY:
                return 'buy';
                break;
            case Indicator::HOLD:
                return 'hold';
                break;
            case Indicator::SELL:
                return 'sell';
                break;
        }
    }

    public function handle()
    {
        $pair = 'ETH-EUR';
        $t = new Ticker();
        $data = $t->getRecentData($pair, 168);

        $config = new Collection(['data' => $data, 'period' => 14]);


        $headers = ['Name', 'signal'];
        $data = [];

        $i = new AverageDirectionalMovementIndexIndicator();
        $r = $i->check($config);
        $data[] = ['AverageDirectionalMovementIndexIndicator', $this->toText($r)];

        $i = new OnBalanceVolumeIndicator();
        $r = $i->check($config);
        $data[] = ['OnBalanceVolumeIndicator', $this->toText($r)];

        $i = new CommodityChannelIndexIndicator();
        $r = $i->check($config);
        $data[] = ['CommodityChannelIndexIndicator', $this->toText($r)];


        $i = new HilbertTransformInstantaneousTrendlineIndicator();
        $r = $i->check($config);
        $data[] = ['HilbertTransformInstantaneousTrendlineIndicator', $this->toText($r)];

        $i = new HilbertTransformTrendVersusCycleModeIndicator();
        $r = $i->check($config);
        $data[] = ['HilbertTransformTrendVersusCycleModeIndicator', $this->toText($r)];

        $i = new MoneyFlowIndexIndicator();
        $r = $i->check($config);
        $data[] = ['MoneyFlowIndexIndicator', $this->toText($r)];


        $i = new MovingAverageCrossoverDivergenceIndicator();
        $r = $i->check($config);
        $data[] = ['MovingAverageCrossoverDivergenceIndicator', $this->toText($r)];

        $this->table($headers, $data);
    }
}
