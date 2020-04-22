<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {

            $enum = ['percent','number'];


            $table->increments('id');
            $table->integer('school_id');
            $table->string('name',20);
            $table->date('start_date');
            $table->date('finish_date');
            $table->integer('register_fee')->comment('报名费');
            $table->tinyInteger('status');
            $table->enum('type',['TIME','PACK']);
            $table->integer('licence_type_id');
            $table->integer('vehicle_type_limit_hours')->comment('限制车型学时');
            $table->integer('indoor_hours');
            $table->integer('outdoor_hours');
            $table->integer('outday_fee');
            $table->boolean('allow_arrears');
            $table->integer('arrears_hours');
            $table->integer('mock_normal_fee');
            $table->integer('mock_holiday_fee');
            $table->enum('online_register_discount',$enum);
            $table->integer('online_register_discount_percent',2);
            $table->integer('online_register_discount_number');
            $table->enum('online_hour_discount',$enum);
            $table->integer('online_hour_discount_percent',2);
            $table->integer('online_hour_discount_number');
            $table->enum('break_fee',$enum);
            $table->integer('break_fee_percent',2);
            $table->integer('break_fee_number');
            $table->integer('subject1_fee');
            $table->integer('subject2_fee');
            $table->integer('subject3_fee');
            $table->integer('subject4_fee');
            $table->timestamps();
            // $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('product');
    }
}
