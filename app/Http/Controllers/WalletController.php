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

        /*
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
         * 
         */
        session(['tab' => $tab]);
        return $this->search($request, $tab);
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

    public function search(Request $request, $tab = 1 ) {

        foreach (['EUR', 'BTC', 'ETH', 'LTC'] as $wallet) {
            $wallets[$wallet] = Wallet::where('wallet', $wallet)->get();
            $orderBuyAvg[$wallet] = Order::whereWallet($wallet)->whereTrade('BUY')->whereFilled(0)->get()->avg('coinprice');
                    
            $buys = Order::whereWallet($wallet)->where('created_at','>=',date('Y-m-d'). ' 00:00:00' )->whereTrade('BUY')->get()->sum('tradeprice');
            $sells = Order::whereWallet($wallet)->where('created_at', '>=',date('Y-m-d') . ' 00:00:00')->whereTrade('SELL')->get()->sum('tradeprice');
            
            $diffSellsBuys[$wallet] = $sells - $buys;

        }

        $ordersFound = Order::Query();
        
       
        $tab = session('tab', $tab);               
               
        $searchBuySell = $request->get('searchBuySell', session('searchBuySell', 'all'));
        session(['searchBuySell' => $searchBuySell]);
        
        
        $searchOpen = $request->get('searchOpen', session('searchOpen', 'all'));
        session(['searchOpen' => $searchOpen]);
        
        
        $searchString = $request->get('searchString',session('searchString'));
        session(['searchString' => $searchString]);
               
        $searchMode = $request->get('searchMode', session('searchMode'));
        session(['searchMode' => $searchMode]);
       
        if ($searchString) {
            if (is_numeric($searchString) || is_float($searchString)) {
                if (in_array($searchMode, ['=', '>', '<'])) {
                    $ordersFound->orWhere('amount', $searchMode, $searchString )
                            ->orWhere('tradeprice',$searchMode, $searchString)
                            ->orWhere('coinprice', $searchMode, $searchString)
                            ->orWhere('fee', $searchMode,$searchString);
                            
                }
            } 
        } 
        
        if($searchOpen == 'open') {
             $ordersFound = $ordersFound->whereTrade('BUY')->whereFilled(0);
        }

        if($searchOpen == 'closed') {
             $ordersFound = $ordersFound->whereTrade('BUY')->whereFilled(1);
        }
        
        if ($searchBuySell == 'buy') {
            $ordersFound = $ordersFound->whereTrade('BUY');
        }
        if ($searchBuySell == 'sell') {
            $ordersFound = $ordersFound->whereTrade('Sell');
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

        return view('wallets.index', compact('wallets', 'orders', 'tab', 'diffSellsBuys','orderBuyAvg', 'searchString','searchMode','searchBuySell','searchOpen'));
    }

}
