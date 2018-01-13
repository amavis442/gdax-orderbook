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
            $table->string('pair',10);
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('position_id')->unsigned()->nullable();
            $table->enum('side',['buy','sell']);
            $table->string('size',20);
            $table->decimal('amount',15,9);
            $table->string('status',40)->nullable();
            $table->string('order_id',40);
            $table->string('strategy',40)->nullable();
            $table->decimal('take_profit',10,2)->nullable();
            $table->string('close_reason',40)->nullable();
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
