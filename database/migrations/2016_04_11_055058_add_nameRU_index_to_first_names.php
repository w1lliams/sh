<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameRUIndexToFirstNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('first_names', function (Blueprint $table) {
            $table->index(['nameRU', 'c'], 'nameru_c');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('first_names', function (Blueprint $table) {
            $table->dropIndex('nameru_c');
        });
    }
}
