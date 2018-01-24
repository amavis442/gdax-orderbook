<?php

namespace App\Http\Controllers;

use http\Env\Request;
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
        $timestamp = \Carbon\Carbon::now('Europe/Amsterdam')->format('Y-m-d H:i:s');

        $result = [];
        switch ($r) {
            case Indicator::BUY:
                $result = ['indicatorname'   => $name,
                           'indicatorsignal' => 'buy',
                           'timestamp'       => $timestamp,
                           'issell'          => false,
                           'isbuy'           => true,
                           'ishold'          => false,
                ];
                break;
            case Indicator::HOLD:
                $result = ['indicatorname'   => $name,
                           'indicatorsignal' => 'hold',
                           'timestamp'       => $timestamp,
                           'issell'          => false,
                           'isbuy'           => false,
                           'ishold'          => true,
                ];
                break;
            case Indicator::SELL:
                $result = ['indicatorname'   => $name,
                           'indicatorsignal' => 'sell',
                           'timestamp'       => $timestamp,
                           'issell'          => true,
                           'isbuy'           => false,
                           'ishold'          => false,
                ];
                break;
        }

        return $result;
    }

    public function getIndicators(Request $request)
    {
        $pair = $request->get('pair');

        $t = new Ticker();
        $recentData = $t->getRecentData($pair, 168);
        $config = new Collection(['data' => $recentData, 'period' => 14]);


        $i = new AverageDirectionalMovementIndexIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('AverageDirectionalMovementIndexIndicator', $r);

        $i = new OnBalanceVolumeIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('OnBalanceVolumeIndicator', $r);

        $i = new CommodityChannelIndexIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('CommodityChannelIndexIndicator', $r);


        $i = new HilbertTransformInstantaneousTrendlineIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('HilbertTransformInstantaneousTrendlineIndicator', $r);

        $i = new HilbertTransformTrendVersusCycleModeIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('HilbertTransformTrendVersusCycleModeIndicator', $r);

        $i = new MoneyFlowIndexIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('MoneyFlowIndexIndicator', $r);


        $i = new MovingAverageCrossoverDivergenceIndicator();
        $r = $i->check($config);
        $data[] = $this->toText('MovingAverageCrossoverDivergenceIndicator', $r);

        return $data;
    }
}
