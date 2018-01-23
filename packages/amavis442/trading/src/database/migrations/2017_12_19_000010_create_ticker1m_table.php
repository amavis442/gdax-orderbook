<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicker1mTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticker_1m', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pair', 10);
            $table->bigInteger('timeid', false,true)->nullable();
            $table->decimal('price', 15, 4)->nullable();
            $table->decimal('open', 15, 4)->nullable();
            $table->decimal('high', 15, 4)->nullable();
            $table->decimal('low', 15, 4)->nullable();
            $table->decimal('close', 15, 4)->nullable();
            $table->decimal('volume', 15, 4)->nullable();
            $table->decimal('volume_30d', 15, 4)->nullable();
            $table->decimal('best_bid', 15, 4)->nullable();
            $table->decimal('best_ask', 15, 4)->nullable();
            $table->unique(['pair','timeid']);

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
        Schema::dropIfExists('ticker_1m');
    }
}
