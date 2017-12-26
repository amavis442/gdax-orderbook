<?php

namespace App\Http\Controllers;

use App\Wallet;
use Illuminate\Http\Request;
use App\Order;

use GDAX\Utilities\GDAXConstants;

class WalletController extends Controller {

    const TAB_PRODUCTS = [1 => 'ALL', 2 => 'BTC-EUR', 3 => 'ETH-EUR', 4 => 'ETH-BTC', 5 => 'LTC-EUR', 6 => 'LTC-BTC'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $tab = 1) {


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

    public function getAccountBalances() {
        $client = new \GDAX\Clients\AuthenticatedClient(
                config('coinbase.api_key'), config('coinbase.api_secret'), config('coinbase.password')
        );

        $balances = [];
        $accounts = $client->getAccounts();

        /** @var  \GDAX\Types\Response\Authenticated\Account $account */
        foreach ($accounts as $account) {
            $currency = $account->getCurrency();
            $balance = $account->getBalance();

            $balances[$currency] = $balance;
        }


        $product = (new \GDAX\Types\Request\Market\Product())->setProductId(\GDAX\Utilities\GDAXConstants::PRODUCT_ID_LTC_EUR);

      
        $fill = (new \GDAX\Types\Request\Authenticated\Fill())->setProductId(\GDAX\Utilities\GDAXConstants::PRODUCT_ID_LTC_EUR);

//        $fills = $client->getFills($fill);
//        dump($fills);

        /* $product = (new \GDAX\Types\Request\Market\Product())->setProductId(\GDAX\Utilities\GDAXConstants::PRODUCT_ID_LTC_EUR);
        $productTicker = $client->getProductTicker($product);
        dump($productTicker); */


        /* $listOrders = (new \GDAX\Types\Request\Authenticated\ListOrders())
                ->setStatus(\GDAX\Utilities\GDAXConstants::ORDER_STATUS_ALL)
                ->setProductId(\GDAX\Utilities\GDAXConstants::PRODUCT_ID_LTC_EUR);

        $orders = $client->getOrders($listOrders);
        dump($orders); */
        
        
//        $product
        //$trades = $client->getTrades($product);

        //dump($trades);

        return $balances;
    }

    public function search(Request $request, $tab = 1) {

        $products = ['BTC' => ['EUR'], 'ETH' => ['BTC', 'EUR'], 'LTC' => ['BTC', 'EUR']];

        foreach (['EUR', 'BTC', 'ETH', 'LTC'] as $wallet) {
            $wallets[$wallet] = Wallet::where('wallet', $wallet)->get();
            if ($wallet == 'EUR') {
                continue;
            }

            foreach ($products[$wallet] as $productId) {
                $pair = $wallet;
                if ($productId <> '') {
                    $pair .= '-' . $productId;
                }
                $orderBuyAvg[$pair] = Order::whereProductId($pair)->whereSide('BUY')->whereFilled(0)->get()->avg('coinprice');

                $buysToday = Order::whereProductId($pair)->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->whereSide('BUY')->get()->sum('tradeprice');
                $sellsToday = Order::whereProductId($pair)->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->whereSide('SELL')->get()->sum('tradeprice');

                $diffSellsBuys['Today'][$pair] = $sellsToday - $buysToday;

                $buysAll = Order::whereProductId($pair)->whereSide('BUY')->get()->sum('tradeprice');
                $sellsAll = Order::whereProductId($pair)->whereSide('SELL')->get()->sum('tradeprice');

                $diffSellsBuys['All'][$pair] = $sellsAll - $buysAll;
            }
        }

        $ordersFound = Order::Query();


        $tab = session('tab', $tab);

        $searchBuySell = $request->get('searchBuySell', session('searchBuySell', 'all'));
        session(['searchBuySell' => $searchBuySell]);


        $searchOpen = $request->get('searchOpen', session('searchOpen', 'all'));
        session(['searchOpen' => $searchOpen]);


        $searchString = $request->get('searchString', session('searchString'));
        session(['searchString' => $searchString]);

        $searchMode = $request->get('searchMode', session('searchMode'));
        session(['searchMode' => $searchMode]);

        if ($searchString) {
            if (is_numeric($searchString) || is_float($searchString)) {
                if (in_array($searchMode, ['=', '>', '<'])) {
                    $ordersFound->orWhere('amount', $searchMode, $searchString)
                            ->orWhere('tradeprice', $searchMode, $searchString)
                            ->orWhere('coinprice', $searchMode, $searchString)
                            ->orWhere('fee', $searchMode, $searchString);
                }
            }
        }

        if ($searchOpen == 'open') {
            $ordersFound = $ordersFound->whereSide('BUY')->whereFilled(0);
        }

        if ($searchOpen == 'closed') {
            $ordersFound = $ordersFound->whereSide('BUY')->whereFilled(1);
        }

        if ($searchBuySell == 'buy') {
            $ordersFound = $ordersFound->whereSide('BUY');
        }
        if ($searchBuySell == 'sell') {
            $ordersFound = $ordersFound->whereSide('Sell');
        }

        $tabProducts = self::TAB_PRODUCTS;
        if ($tab != 1) {
            $ordersFound->whereProductId($tabProducts[$tab]);
        }

        if (!isset($ordersFound)) {
            $ordersFound = Order::Query();
        }

        $orders = $ordersFound->orderBy('created_at', 'desc')
                ->paginate(); // Filled orders.

        $balances = $this->getAccountBalances();

        return view('wallets.index', compact('wallets', 'balances', 'orders', 'tabProducts', 'tab', 'diffSellsBuys', 'orderBuyAvg', 'searchString', 'searchMode', 'searchBuySell', 'searchOpen'));
    }

}
