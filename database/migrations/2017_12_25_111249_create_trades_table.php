<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trade_id')->unsigned();
            $table->string('product_id',7);
            $table->string("order_id",36);
            $table->string("user_id",24);
            $table->string("profile_id",36);
            $table->string("liquidity",1);
            $table->decimal("price",15,8);
            $table->decimal("size",15,8);
            $table->decimal("fee",15,8);
            $table->string("side",4);
            $table->boolean("settled");
            $table->string("usd_volume",20);
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
        Schema::dropIfExists('trades');
    }
}
