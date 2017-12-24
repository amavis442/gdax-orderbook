<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('wallet',['EUR','BTC','ETH','LTC']);
                          
            $table->enum('trade',['BUY','SELL']);
            $table->decimal('amount',15,8)->nullable();
            $table->decimal('tradeprice',15,8)->nullable();
            $table->decimal('coinprice',15,8)->nullable(); //spread and shit can coz less
            $table->decimal('fee',8,2)->nullable()->default('0.00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
