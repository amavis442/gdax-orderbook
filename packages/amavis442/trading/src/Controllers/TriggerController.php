<?php

namespace Amavis442\Trading\Controllers;

use Amavis442\Trading\Contracts\IndicatorManagerInterface;
use Amavis442\Trading\Util\Indicators;
use Illuminate\Http\Request;
use Amavis442\Trading\Models\Ticker1m;


class TriggerController extends Controller
{
    protected $indicatorManager;

    public function setIndicatorManager(IndicatorManagerInterface $indicatorManager)
    {
        $this->indicatorManager = $indicatorManager;
    }

    public function getSignal(Request $request, $signal, IndicatorManagerInterface $indicatorManager) {
        $ticker = new Ticker1m();
        $tickerData = $ticker->getRecentData('BTC-EUR');

        $data = $indicatorManager->mfi($tickerData);

        return ['result' => $data];
    }

}

