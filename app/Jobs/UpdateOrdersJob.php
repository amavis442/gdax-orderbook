<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Coinbase\CoinbaseExchange;
use App\Order;
use App\Services\OrderService;


class UpdateOrdersJob implements ShouldQueue
{

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    protected $orderService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $coinbase = new CoinbaseExchange(config('coinbase.api_key'), config('coinbase.api_secret'), config('coinbase.password'));
        $response = $coinbase->getFills();
        $orders   = [];

        $client = new \GDAX\Clients\AuthenticatedClient(
            config('coinbase.api_key'),
            config('coinbase.api_secret'),
            config('coinbase.password')
        );



        foreach ($response as $orderfilled) {
            $order = Order::whereTradeId($orderfilled->trade_id)->first();
            if (!$order) {
                $orders[$orderfilled->trade_id] = $orderfilled;
            }
        }
        ksort($orders);

        if (count($orders) > 0) {
            foreach ($orders as $trade_id => $orderfilled) {
                $data['amount'] = $orderfilled->size;

                $inputData = serialize($orderfilled);

                $data               = [];
                $data['product_id'] = $orderfilled->product_id;
                $data['side']       = strtoupper($orderfilled->side);
                $data['amount']     = $orderfilled->size;
                $data['coinprice']  = $orderfilled->price;
                $data['tradeprice'] = $orderfilled->size * $orderfilled->price;
                $data['fee']        = $orderfilled->fee;
                $data['orderhash']  = $orderfilled->order_id;
                $data['trade_id']   = $orderfilled->trade_id;
                $data['created_at'] = \Carbon\Carbon::parse($orderfilled->created_at)->format('Y-m-d H:i:s');
                $data['raw']        = $inputData;

                $this->orderService->create($data);



                $accounts = $client->getAccounts();
                /** @var  \GDAX\Types\Response\Authenticated\Account $account */
                foreach ($accounts as $account){
                    $currency = $account->getCurrency();
                    $balance = $account->getBalance();

                    $balances[$currency] = $balance;
                }

                $balanceWallet          = [];
                $wallet                 = substr($orderfilled->product_id, 0, 3);
                $balanceWallet[$wallet] = $balances[$wallet];

                $wallet                 = substr($orderfilled->product_id, 4, 3);
                $balanceWallet[$wallet] = $balances[$wallet];


                dispatch(new SendTelegramJob($data, $balanceWallet));
            }
        }
    }
}
