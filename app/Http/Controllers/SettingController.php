<?php

namespace App\Http\Controllers;

use Amavis442\Trading\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrFail();

        return view('settings.index',compact('setting'));
    }

    public function update(Request $request, Setting $setting)
    {
        $setting->botactive = $request->get('botactive',0);
        $setting->trailingstop = $request->get('trailingstop',30);
        $setting->max_orders = $request->get('max_orders',1);
        $setting->tradebottomlimit = $request->get('bottom',10000);
        $setting->tradetoplimit = $request->get('top',12000);
        $setting->order_minimal_size = $request->get('order_minimal_size',0.001);

        $setting->save();

        return redirect()->route('settings.index')->with('msg','Saved');
    }


    public function getSetting(Request $request)
    {
        $pair = $request->get('pair');

        return Setting::wherePair($pair)->first();
    }

    public function updateSetting(Request $request)
    {
        $pair = $request->get('pair');
        $setting  = Setting::wherePair($pair)->first();

        $setting->botactive = $request->get('botactive',0);
        $setting->trailingstop = $request->get('trailingstop',30);
        $setting->max_orders = $request->get('max_orders',1);
        $setting->tradebottomlimit = $request->get('tradebottomlimit',10000);
        $setting->tradetoplimit = $request->get('tradetoplimit',12000);
        $setting->order_minimal_size = $request->get('order_minimal_size',0.001);
        $setting->sellstradle = $request->get('sellstradle',0.001);
        $setting->buystradle = $request->get('buystradle',0.001);
        $setting->save();

        return ['result'=>'ok'];
    }
}
