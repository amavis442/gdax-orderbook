<?php
return [
    'password' => env('GDAX_PASSWORD'),
    'api_secret' => env('GDAX_API_SECRET'),
    'api_key' => env('GDAX_API_KEY'),
    'endpoint' => env('GDAX_ENDPOINT'),
    'sandbox' => env('GDAX_SANDBOX', false),
    'coin' => env('CRYPTOCOIN', 'BTC-EUR'),
    'ltc_spread' => 0.05,
    'btc_spread' => 0.0001,
    'eth_spread' => 0.01,
    'lowerlimit' => env('LOWERLIMIT', 90100),
    'upperlimit' => env('UPPERLIMIT', 10300),
    'stradle' => env('STRADLE', 0.03),
];
