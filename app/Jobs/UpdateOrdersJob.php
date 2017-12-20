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

class UpdateOrdersJob implements ShouldQueue {

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
    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $coinbase = new CoinbaseExchange(config('coinbase.api_key'), config('coinbase.api_secret'), config('coinbase.password'));
        $response = $coinbase->getFills();



        foreach ($response as $orderfilled) {
            $order = Order::whereOrderhash($orderfilled->order_id)->first();
            if (!$order) {
                $data = [];
                $data['wallet'] = substr($orderfilled->product_id, 0, 3);
                $data['trade'] = strtoupper($orderfilled->side);
                $data['amount'] = $orderfilled->size;
                $data['coinprice'] = $orderfilled->price;
                $data['tradeprice'] = $data['amount'] * $data['coinprice'];
                $data['fee'] = $orderfilled->fee;
                $data['orderhash'] = $orderfilled->order_id;

                $data['created_at'] = \Carbon\Carbon::parse($orderfilled->created_at)->format('Y-m-d H:i:s');
                
      
                $this->orderService->create($data);
            }
        }
    }

}
