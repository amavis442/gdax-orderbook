<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
return [
    'currency' => env('CURRENCY','EUR'),
    'password' => env('GDAX_PASSWORD'),
    'api_secret' => env('GDAX_API_SECRET'),
    'api_key' => env('GDAX_API_KEY'),
    'endpoint' => env('GDAX_ENDPOINT'),
    'ltc_spread' => 0.05,
    'btc_spread' => 0.0001,
    'eth_spread' => 0.01
];
