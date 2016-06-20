<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fio', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('nameRU');
            $table->integer('c')->default(1);
            $table->timestamps();
            $table->unique(['name', 'nameRU']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fio');
    }
}
