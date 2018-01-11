<?php

return [
    'password'   => env('GDAX_PASSWORD'),
    'api_secret' => env('GDAX_API_SECRET'),
    'api_key'    => env('GDAX_API_KEY'),
    'endpoint'   => env('GDAX_ENDPOINT'),
    'sandbox'    => env('GDAX_SANDBOX', false),
    'ltc_spread' => 0.05,
    'btc_spread' => 0.0001,
    'eth_spread' => 0.01
];