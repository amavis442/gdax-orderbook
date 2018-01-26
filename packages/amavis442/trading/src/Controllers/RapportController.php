<?php

namespace Amavis442\Trading\Controllers;

use Illuminate\Http\Request;
use App\Order;

class RapportController extends Controller
{
    public function index()
    {
        $wallets = [];

        foreach (['BTC', 'ETH', 'LTC'] as $wallet) {
            $Buy = Order::whereWallet($wallet)->where('trade', 'BUY')->get();
            $BuyFees = $Buy->sum('fee');

            $Sell = Order::whereWallet($wallet)->where('trade', 'SELL')->get();
            $SellFees = $Sell->sum('fee');

            $buytrade = $Buy->sum('tradeprice');
            $selltrade = $Sell->sum('tradeprice');
            $diff = $selltrade - $buytrade;

            $wallets[$wallet] = [
                'buyfees' => $BuyFees,
                'buytrade' => $buytrade,
                'sellfees' => $SellFees,
                'selltrade' => $selltrade,
                'diff' => $diff
            ];
        }

        return view('analysis.index', compact('wallets'));
    }
}
