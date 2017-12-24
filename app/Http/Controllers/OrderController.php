<?php

namespace App\Http\Controllers;

use App\Order;
use App\Wallet;
use Illuminate\Http\Request;

class OrderController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $orders = Order::all()->paginate(); // Filled orders.



        return view('orders.index', compact('orders'));
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order) {
        
        return view('orders.edit', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order) {
        
        $order->trade = $request->get('trade');
        $order->wallet = $request->get('wallet');
        $order->amount = $request->get('amount');
        $order->coinprice = $request->get('coinprice');
        $order->tradeprice = $request->get('tradeprice');
        $order->fee = $request->get('fee');
        $order->soldfor = $request->get('soldfor',0.0);
        
        if ($order->soldfor > 0) {
            $order->profit = number_format(($order->soldfor - $order->coinprice) *  $order->amount,2);
        }
        
        $order->orderhash = $request->get('orderhash');
        if ($request->has('filled')) {
            $order->filled = true;
        } else {
            $order->filled = false;
        }
        
        $order->save();

        if ($order->trade == 'BUY') {            
            // Bijschrijven bij gekozen wallet
            $wallet = $order->wallet($order->wallet)->first();
            $wallet->wallet = $order->wallet;
            $wallet->currency = $order->amount;
            $wallet->status = 'DEPOSIT';
            $order->wallet()->save($wallet);

            // Afschrijven van de EURO rekening
            $currencyWallet = $order->wallet(config('coinbase.currency'))->first();
            $currencyWallet->currency = -$order->tradeprice;
            $currencyWallet->status = 'WITHDRAW';
            $order->wallet()->save($currencyWallet);
        }


        if ($order->trade == 'SELL') {
            // Afschrijven bij gekozen wallet
            $wallet = $order->wallet($order->wallet)->first();
            $wallet->wallet = $order->wallet;
            $wallet->currency = -$order->amount;
            $wallet->status = 'WITHDRAW';
            $order->wallet()->save($wallet);

            // Bijschrijven van de EURO rekening
            $currencyWallet = $order->wallet(config('coinbase.currency'))->first();
            $currencyWallet->currency = $order->tradeprice - $order->fee;
            $currencyWallet->status = 'DEPOSIT';
            $order->wallet()->save($currencyWallet);
        }
        
        return redirect()->route('wallets.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order) {
        $order->wallet('all')->delete();
        $order->delete();

        return redirect()->route('wallets.index');
    }

}
