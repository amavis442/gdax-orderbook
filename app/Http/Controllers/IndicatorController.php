<?php

namespace App\Http\Controllers;

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
 * Class IndicatorController
 *
 * @package App\Http\Controllers
 */
class IndicatorController extends Controller
{

    public function index()
    {
        return view('indicators.index');
    }

    public function toText($name, $r)
    {
        $result = [];
        switch ($r) {
            case Indicator::BUY:
                $result = ['name' => $name, 'signal'=>'buy','styleclass'=>'alert alert-info'];
                break;
            case Indicator::HOLD:
                $result = ['name' => $name, 'signal'=>'hold','styleclass'=>'alert alert-info'];
                break;
            case Indicator::SELL:
                $result = ['name' => $name, 'signal'=>'hold','styleclass'=>'alert alert-danger'];
                break;
        }

        return $result;
    }

    public function getIndicators() {
        $pair = 'ETH-EUR';
        $t = new Ticker();
        $data = $t->getRecentData($pair,168);
        $config = new Collection(['data' => $data, 'period' => 14]);

        $i = new AverageDirectionalMovementIndexIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('AverageDirectionalMovementIndexIndicator',$r);

        $i = new OnBalanceVolumeIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('OnBalanceVolumeIndicator',$r);

        $i = new CommodityChannelIndexIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('CommodityChannelIndexIndicator', $r);


        $i = new HilbertTransformInstantaneousTrendlineIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('HilbertTransformInstantaneousTrendlineIndicator', $r);

        $i = new HilbertTransformTrendVersusCycleModeIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('HilbertTransformTrendVersusCycleModeIndicator',$r);

        $i = new MoneyFlowIndexIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('MoneyFlowIndexIndicator', $r);


        $i = new MovingAverageCrossoverDivergenceIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('MovingAverageCrossoverDivergenceIndicator', $r);

        return $data;
    }
}
