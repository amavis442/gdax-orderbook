<?php

namespace App\Http\Controllers;

use Amavis442\Trading\Bot\PositionBot;
use Amavis442\Trading\Models\Position;
use Amavis442\Trading\Models\Setting;
use Amavis442\Trading\Indicators\Stoploss;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $page = 1)
    {
        $orders = Position::select('*')
            ->where('status', '!=', 'closed')
            ->orderBy('created_at', 'desc')->paginate(); // positions.

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
        try {
            $result = $positionBot->sellPosition($position);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $e->getMessage();
        }
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

        try {
            $result = $positionBot->setTrailing($position);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $e->getMessage();
        }
        if ($result) {
            return ['result' => 'ok'];
        } else {
            return ['result' => 'notok', 'msg' => 'Placing sell order failed. See logging why'];
        }
    }

    public function getTrailing(Request $request, PositionBot $positionBot, Stoploss $stoploss)
    {

        $setting = new Setting();

        $positions = Position::whereStatus('trailing')->get();

        try {
            $positions = $positions->map(function ($position, $key) use ($positionBot, $setting, $stoploss) {
                try {
                    $currentPrice = Cache::get(
                        'gdax::' . $position->pair . '::currentprice',
                        $positionBot->getCurrentPrice()
                    );
                    Cache::put('gdax::' . $position->pair . '::currentprice', $currentPrice, 10);
                } catch (\Exception $e) {
                    Log::error($e->getTraceAsString());

                    return $e->getMessage();
                }

                $position->currentprice = $currentPrice;
                $position->trailingstoptrigger = $stoploss->check($currentPrice, $position, $setting);
                $cacheKey = 'gdax::stoploss::' . $position->id;
                $trailingstopprice = Cache::get($cacheKey, 0.0);
                $position->trailingstopprice = $trailingstopprice;

                return $position;
            });
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());

            return $e->getMessage();
        }

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
