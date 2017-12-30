<?php

namespace App\Services;

use App\Order;
use App\Wallet;

class OrderService {

    public function getBalance($wallet) {
        return Wallet::where('wallet', $wallet)->sum('currency');
    }

    
    public function create(Array $data) {
        $order = new Order();
        $order->product_id = $data['product_id'];
        $order->amount = $data['amount'];
        $order->coinprice = $data['coinprice'];
        $order->tradeprice = $data['tradeprice'];
        $order->fee = $data['fee'];
        $order->side = $data['side'];
        $order->orderhash = $data['orderhash'];
        $order->trade_id = $data['trade_id'];

        $order->filled = false;
        $order->raw = $data['raw'];

        if (isset($data['created_at'])) {
            $order->created_at = $data['created_at'];
        }


        if ($order->side == 'BUY') {
            $fromWallet = substr($order->product_id, 4, 3);
            $toWallet = substr($order->product_id, 0, 3);
        } else {
            $fromWallet = substr($order->product_id, 0, 3);
            $toWallet = substr($order->product_id, 4, 3);
        }
        $order->wallet = $fromWallet;
        $order->save();

        if ($order->side == 'BUY') {
            // Bijschrijven bij gekozen wallet
            $wallet = new Wallet();
            $wallet->wallet = $toWallet;
            $wallet->currency = $order->amount;
            $wallet->status = 'DEPOSIT';
            $order->wallet($toWallet)->save($wallet);

            // Afschrijven van wallet
            $currencyWallet = new Wallet();
            $currencyWallet->wallet = $fromWallet;
            $currencyWallet->currency = -$order->tradeprice;
            $currencyWallet->status = 'WITHDRAW';
            $order->wallet($fromWallet)->save($currencyWallet);
        }


        if ($order->side == 'SELL') {
            // Bijschrijven van wallet
            $currencyWallet = new Wallet();
            $currencyWallet->wallet = $toWallet;
            $currencyWallet->currency = $order->tradeprice - $order->fee;
            $currencyWallet->status = 'DEPOSIT';
            $order->wallet($toWallet)->save($currencyWallet);

            // Afschrijven bij gekozen wallet
            $wallet = new Wallet();
            $wallet->wallet = $fromWallet;
            $wallet->currency = -$order->amount;
            $wallet->status = 'WITHDRAW';
            $order->wallet($fromWallet)->save($wallet);
        }
    }

    /**
     * @deprecated
     * 
     * @param Order $order
     * @param array $data
     */
    public function update(Order $order, Array $data) {
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
