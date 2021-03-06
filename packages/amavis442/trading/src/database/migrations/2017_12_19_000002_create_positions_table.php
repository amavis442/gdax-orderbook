<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pair', 10);
            $table->string('order_id', 40);
            $table->string('size', 20);
            $table->decimal('amount', 15, 9);
            $table->decimal('open', 15, 9);
            $table->decimal('close', 15, 9)->nullable();
            $table->decimal('sellfor', 15, 2)->nullable();
            $table->decimal('trailingstop', 5, 2)->nullable();
            $table->enum('status', ['open', 'pending','trailing', 'closed'])->default('open');
            $table->string('close_reason', 20)->nullable();
            $table->boolean('watch')->default(true);
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
        Schema::dropIfExists('positions');
    }
}
