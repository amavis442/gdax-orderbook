<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::resource('wallets', 'WalletController')->middleware(['auth']);
Route::get('/getwallets', 'WalletController@getWallets')->middleware('auth');

Route::resource('signals', 'SignalController')->middleware(['auth']);
Route::get('/getsignal/{signal}', 'SignalController@getSignal')->middleware('auth');

Route::get('/getorders/{page?}', 'OrderController@index')->middleware('auth');
Route::get('/getpositions/{page?}', 'PositionController@index')->middleware('auth');
Route::get('/gettrailing/{page?}', 'PositionController@getTrailing')->middleware('auth');

Route::post('/updateposition', 'PositionController@update')->middleware('auth');

Route::post('/sellposition','PositionController@sellPosition')->middleware('auth');
Route::post('/trailingposition','PositionController@trailingPosition')->middleware('auth');

Route::resource('settings', 'SettingController')->middleware('auth');

Route::resource('users', 'UserController')->middleware('auth');

Route::post('/updatesetting', 'SettingController@updateSetting')->middleware('auth');
Route::get('/getsetting', 'SettingController@getSetting')->middleware('auth');

Route::get('/getindicators', 'IndicatorController@getIndicators')->middleware('auth');
Route::get('/indicators', 'IndicatorController@index')->middleware('auth')->name('indicators.index');


Route::get('/heartbeat', '\Amavis442\Trading\Controllers\BotController@heartbeat')->middleware('auth')->name('bot.heartbeat');
Route::get('/currentprices', '\Amavis442\Trading\Controllers\BotController@currentprices')->middleware('auth')->name('bot.currentprices');