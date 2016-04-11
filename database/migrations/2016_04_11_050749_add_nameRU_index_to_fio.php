<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameRUIndexToFio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fio', function (Blueprint $table) {
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
        Schema::table('fio', function (Blueprint $table) {
            $table->dropIndex('nameru_c');
        });
    }
}
