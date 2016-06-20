<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirstNameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('first_names', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nameUA');
            $table->string('nameRU');
            $table->integer('c')->default(1);
            $table->timestamps();
            $table->unique(['nameUA', 'nameRU']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('first_names');
    }
}
