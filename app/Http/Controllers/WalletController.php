<?php

namespace App\Http\Controllers;

use App\Wallet;
use Illuminate\Http\Request;
use App\Order;

class WalletController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $tab = 1) {

        foreach (['EUR', 'BTC', 'ETH', 'LTC'] as $wallet) {
            $wallets[$wallet] = Wallet::where('wallet', $wallet)->get();
            $orderBuyAvg[$wallet] = Order::whereWallet($wallet)->whereTrade('BUY')->whereFilled(0)->get()->avg('coinprice');
        }

        switch ($tab) {
            case 1:
                $orderTab = ['EUR', 'BTC', 'ETH', 'LTC'];
                break;
            case 2:
                $orderTab = ['BTC'];
                break;
            case 3:
                $orderTab = ['ETH'];
                break;
            case 4:
                $orderTab = ['LTC'];
                break;
            default:
                $orderTab = ['EUR', 'BTC', 'ETH', 'LTC'];
                break;
        }
        $orders = Order::orderBy('created_at', 'desc')->whereIn('wallet', $orderTab)->paginate(); // Filled orders.

        return view('wallets.index', compact('wallets', 'orders', 'tab', 'orderBuyAvg'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $wallet = new Wallet();
        $wallet->wallet = $request->get('walletname');
        $action = $request->get('action');

        return view('wallets.create', compact('wallet', 'action'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $name = $request->get('walletname');
        $action = $request->get('action');
        $currency = $request->get('currency');
        $fee = $request->get('fee');

        $wallet = new Wallet();
        $wallet->wallet = $name;
        if ($action == 'DEPOSIT') {
            $wallet->status = 'DEPOSIT';
            $wallet->currency = $currency;
        }
        if ($action == 'WITHDRAW') {
            $wallet->status = 'WITHDRAW';
            $wallet->currency = -$currency;
        }
        $wallet->save();

        if ($fee > 0.0) {
            $walletFee = new Wallet();
            $walletFee->wallet = 'EUR';
            $walletFee->status = 'WITHDRAW';
            $walletFee->currency = -$fee;
            dump($wallet->fee());
            $wallet->fee()->save($walletFee);
        }

        return redirect()->route('wallets.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function show(Wallet $wallet) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function edit(Wallet $wallet) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wallet $wallet) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wallet $wallet) {
        //
    }

    public function search(Request $request) {

        foreach (['EUR', 'BTC', 'ETH', 'LTC'] as $wallet) {
            $wallets[$wallet] = Wallet::where('wallet', $wallet)->get();
            $orderBuyAvg[$wallet] = Order::whereWallet($wallet)->whereTrade('BUY')->whereFilled(0)->get()->avg('coinprice');
        }

        $tab = $request->get('tab', 1);

        if ($request->has('searchstr')) {
            $searchString = $request->get('searchstr');
            $searchMode = $request->get('searchmode');
            session(['searchString' => $searchString, 'searchMode' => $searchMode]);
        } else {
            $searchString = session('searchString');
            $searchMode = session('searchMode');
        }

        if (is_numeric($searchString) || is_float($searchString)) {
            if (in_array($searchMode, ['=', '>', '<'])) {
                $ordersFound = Order::orWhere('amount', $searchString, $searchMode)
                        ->orWhere('tradeprice', $searchString, $searchMode)
                        ->orWhere('coinprice', $searchString, $searchMode)
                        ->orWhere('fee', $searchString, $searchMode);
            }
        } else {
            $ordersFound = Order::orWhere('trade', $searchString);
        }

        if ($tab != 1) {
            $w = [2 => 'BTC', 3 => 'ETH', 4 => 'LTC'];
            $ordersFound->whereWallet($w[$tab]);
        }
        if (!isset($ordersFound)) {
            $ordersFound = Order::Query();
        }
        $orders = $ordersFound->orderBy('created_at', 'desc')
                ->paginate(); // Filled orders.

        return view('wallets.index', compact('wallets', 'orders', 'tab', 'orderBuyAvg'));
    }

}