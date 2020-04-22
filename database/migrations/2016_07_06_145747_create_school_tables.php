<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school', function (Blueprint $table) {
            $table->increments('id');
            $table->string('school_name');
            $table->string('contacts','20');
            $table->char('contact_phone','11');
            $table->string('tel_phone','12');
            $table->string('fax','18');
            $table->integer('province_id');
            $table->integer('city_id');
            $table->integer('county_area_id');
            $table->string('detail_address');
            $table->smallInteger('status');
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
        Schema::drop('school');
    }
}
