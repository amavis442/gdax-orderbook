<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterOrdersTableRenameTradeToSide extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::raw('ALTER TABLE orders CHANGE trade side enum("BUY","SELL")');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::raw('ALTER TABLE orders CHANGE side trade  enum("BUY","SELL")');
    }

}
