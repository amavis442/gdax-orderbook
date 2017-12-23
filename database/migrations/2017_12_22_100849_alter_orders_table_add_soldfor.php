<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrdersTableAddSoldfor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function(Blueprint $table){
            $table->decimal('soldfor',15,8)->nullable()->after('fee'); // For closing positions
            $table->decimal('profit',10,2)->signed()->nullable()->after('soldfor'); // For closing positions
            
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
            $table->dropColumn('soldfor');
            $table->dropColumn('profit');
        });
    }
}
