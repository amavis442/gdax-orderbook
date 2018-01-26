<?php

namespace Amavis442\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pair', 8)->nullable();
            $table->dateTime('buckettime')->nullable();
            $table->decimal('low', 15, 2)->nullable();
            $table->decimal('high', 15, 2)->nullable();
            $table->decimal('open', 15, 2)->nullable();
            $table->decimal('close', 15, 2)->nullable();
            $table->decimal('volume', 15, 2)->nullable();
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
        Schema::dropIfExists('histories');
    }
}
