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
            $table->string('pair',10);
            $table->decimal('trailingstop', 4, 2)->default(10.00);
            $table->integer('max_orders')->default(1);
            $table->decimal('tradebottomlimit', 10, 2)->default('10000');
            $table->decimal('tradetoplimit', 10, 2)->default('15000');
            $table->string('minimal_order_size', 10)->default('0.001');
            $table->decimal('sellstradle', 4, 2)->default(10.00);
            $table->decimal('buystradle', 4, 2)->default(10.00);
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
