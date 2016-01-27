<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('fullName', 512);
            $table->integer('status_id');
            $table->integer('edrpou');
            $table->integer('opf_id');
            $table->integer('type_id');
            $table->integer('city_id');
            $table->integer('postCode');
            $table->string('address', 512);
            $table->string('email', 512);
            $table->string('phone', 512);

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
        Schema::drop('organizations');
    }
}
