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

Route::resource('orders','OrderController')->middleware(['auth']);

Route::resource('wallets','WalletController')->middleware(['auth']);
Route::get('/wallets/tab/{tab?}','WalletController@index')->middleware(['auth'])->name('wallets.index.tab');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/rap', 'RapportController@index');


