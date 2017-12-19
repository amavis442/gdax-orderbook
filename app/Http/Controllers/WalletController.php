<?php

namespace App\Http\Controllers;

use App\Wallet;
use Illuminate\Http\Request;

use App\Order;


class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        foreach (['EUR','BTC','ETH','LTC'] as $wallet) {
            $wallets[$wallet] = Wallet::where('wallet',$wallet)->get();
        }
        
        $orders = Order::orderBy('created_at')->paginate(); // Filled orders.
        
        return view('wallets.index', compact('wallets','orders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $wallet = new Wallet();
        $wallet->wallet = $request->get('walletname');
        $action = $request->get('action');
        
        return view('wallets.create', compact('wallet','action'));
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
    public function show(Wallet $wallet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function edit(Wallet $wallet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wallet $wallet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Wallet  $wallet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wallet $wallet)
    {
        //
    }
}
