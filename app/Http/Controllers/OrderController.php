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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $order = new Order();

        $trade = 'BUY';
        if ($request->has('trade')) {
            $trade = $request->get('trade');
        }
        
        $wallet = 'BTC';
        if ($request->has('wallet')) {
            $wallet = $request->get('wallet');
        }
        
        return view('orders.create', compact('order', 'trade','wallet'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $order = new Order();
        $order->trade = $request->get('trade');
        $order->wallet = $request->get('wallet');
        $order->amount = $request->get('amount');
        $order->coinprice = $request->get('coinprice');
        $order->tradeprice = $request->get('tradeprice');
        $order->fee = $request->get('fee');
        $order->save();

        if ($order->trade == 'BUY') {
            // Bijschrijven bij gekozen wallet
            $wallet = new Wallet();
            $wallet->wallet = $order->wallet;
            $wallet->currency = $order->amount;
            $wallet->status = 'DEPOSIT';
            $order->wallet()->save($wallet);

            // Afschrijven van de EURO rekening
            $currencyWallet = new Wallet();
            $currencyWallet->wallet = config('coinbase.currency');
            $currencyWallet->currency = -$order->tradeprice;
            $currencyWallet->status = 'WITHDRAW';
            $order->wallet()->save($currencyWallet);
        }


        if ($order->trade == 'SELL') {
            // Afschrijven bij gekozen wallet
            $wallet = new Wallet();
            $wallet->wallet = $order->wallet;
            $wallet->currency = -$order->amount;
            $wallet->status = 'WITHDRAW';
            $order->wallet()->save($wallet);

            // Bijschrijven van de EURO rekening
            $currencyWallet = new Wallet();
            $currencyWallet->wallet = config('coinbase.currency');
            $currencyWallet->currency = $order->tradeprice - $order->fee;
            $currencyWallet->status = 'DEPOSIT';
            $order->wallet()->save($currencyWallet);
        }

        return redirect()->route('wallets.index');
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order) {
        $order->wallet()->delete();
        $order->delete();

        return redirect()->route('wallets.index');
    }

}
