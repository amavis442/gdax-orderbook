<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('spread', 8, 2)->nullable();
            $table->decimal('sellspread', 8, 2)->nullable();
            $table->decimal('stoploss', 4, 2)->default(3);
            $table->decimal('takeprofit', 4, 2)->default(0.5);
            $table->decimal('takeprofittreshold', 4, 2)->default(10.00);
            $table->integer('max_orders')->default(1);
            $table->decimal('bottom', 10, 2)->default('10000');
            $table->decimal('top', 10, 2)->default('15000');;
            $table->string('size', 10)->default('0.0001');
            $table->integer('lifetime')->default(90);
            $table->boolean('botactive')->default(true);
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
        Schema::dropIfExists('settings');
    }
}
