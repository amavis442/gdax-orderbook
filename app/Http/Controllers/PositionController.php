<?php

namespace App\Http\Controllers;

use Amavis442\Trading\Bot\PositionBot;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Setting;
use Amavis442\Trading\Triggers\Stoploss;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PositionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $page = 1)
    {
        $orders = Position::select('*')->orderBy('created_at', 'desc')->paginate(); // positions.

        return $orders;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order $order
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Position $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order $order
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Position $order)
    {
        //
    }

    public function update(Request $request)
    {
        $position = Position::findOrFail($request->get('id'));
        $position->sellfor = $request->get('sellfor');
        $position->trailingstop = $request->get('trailingstop');
        $position->watch = $request->get('watch', 1) ? 1 : 0;
        $position->save();

        return ['result' => 'ok'];
    }

    public function sellPosition(Request $request, PositionBot $positionBot)
    {
        // Just to be sure we have the latest data
        $position = Position::findOrFail($request->get('id'));
        $position->sellfor = $request->get('sellfor');
        $position->trailingstop = $request->get('trailingstop');
        $position->watch = $request->get('watch', 1) ? 1 : 0;
        $position->save();

        $result = $positionBot->sellPosition($position);
        if ($result) {
            return ['result' => 'ok'];
        } else {
            return ['result' => 'failed', 'msg' => 'Placing sell order failed. See logging why'];
        }
    }

    public function trailingPosition(Request $request, PositionBot $positionBot)
    {
        // Just to be sure we have the latest data
        $position = Position::findOrFail($request->get('id'));
        $position->sellfor = $request->get('sellfor');
        $position->trailingstop = $request->get('trailingstop');
        $position->watch = $request->get('watch', 1) ? 1 : 0;
        $position->save();

        $result = $positionBot->setTrailing($position);
        if ($result) {
            return ['result' => 'ok'];
        } else {
            return ['result' => 'notok', 'msg' => 'Placing sell order failed. See logging why'];
        }
    }

    public function getTrailing(Request $request, PositionBot $positionBot, Stoploss $stoploss)
    {
        $currentPrice = $positionBot->getCurrentPrice();
        $setting = new Setting();

        $positions = Position::whereStatus('trailing')->get();

        $positions = $positions->map(function ($position, $key) use ($setting, $currentPrice, $stoploss) {
            $position->currentprice = $currentPrice;
            $position->trailingstoptrigger = $stoploss->signal($currentPrice, $position, $setting);
            $cacheKey = 'gdax.stoploss.' . $position->id;
            $trailingstopprice = Cache::get($cacheKey, 0.0);
            $position->trailingstopprice = $trailingstopprice;

            return $position;
        });

        return $positions;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order $order
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Position $order)
    {
        //
    }

}
