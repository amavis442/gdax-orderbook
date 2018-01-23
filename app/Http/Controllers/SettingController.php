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
        $setting->bottom = $request->get('bottom',10000);
        $setting->top = $request->get('top',12000);
        $setting->size = $request->get('size',0.001);

        $setting->save();

        return redirect()->route('settings.index')->with('msg','Saved');
    }

}
