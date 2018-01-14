<?php

namespace App\Http\Controllers;

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



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        return view('signals.index');
    }

    public function getSignal(Request $request, $signal, IndicatorManagerInterface $indicatorManager) {
        $ticker = new Ticker1m();
        $tickerData = $ticker->getRecentData('BTC-EUR');

        $data = $indicatorManager->mfi($tickerData);

        return ['result' => $data];
    }

}

