<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function(Blueprint $table){
            $table->integer('parent_id')->unsigned()->nullable()->after('id'); // For closing positions
            $table->foreign('parent_id')->references('id')->on('orders');
            $table->boolean('filled')->default(false)->after('fee');
            $table->string('orderhash')->nullable()->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function(Blueprint $table){
            $table->dropColumn('parent_id');
            $table->dropColumn('filled');
            $table->dropColumn('orderhash');
        });
    }
}
