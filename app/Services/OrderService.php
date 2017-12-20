<?php
namespace App\Services;

use App\Order;
use App\Wallet;

class OrderService {
    
    public function create(Array $data)
    {
        $order = new Order();
        $order->trade = $data['trade'];
        $order->wallet = $data['wallet'];
        $order->amount = $data['amount'];
        $order->coinprice = $data['coinprice'];
        $order->tradeprice = $data['tradeprice'];
        $order->fee = $data['fee'];
        
        $order->orderhash = $data['orderhash'];
        $order->filled = false;
        
        if(isset($data['created_at'])) {
            $order->created_at = $data['created_at'];
        }
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
    }
    
    
    public function update(Order $order, Array $data)
    {        
        $order->trade = $data['trade'];
        $order->wallet = $data['wallet'];
        $order->amount = $data['amount'];
        $order->coinprice = $data['coinprice'];
        $order->tradeprice = $data['tradeprice'];
        $order->fee = $data['fee'];
        $order->orderhash = $data['orderhash'];
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
    }
}
