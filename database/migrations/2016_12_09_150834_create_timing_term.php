<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimingTerm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timing_term', function (Blueprint $table) {
            $table->increments('id');
            $table->string('term_numbers',16)->comment('计时终端统一编号');
            $table->enum('term_type',[1,2,3])->comment('计时终端类型');
            $table->string('vender',128)->comment('生成厂家');
            $table->string('imei',128)->comment('终端IMEI号或设备MAC地址');
            $table->string('sn',128)->comment('终端出厂序列号');
            $table->string('vehicle_numbers',16)->comment('车辆统一编号');
            $table->string('sim',16)->comment('终端sim卡号');
            $table->tinyInteger('status')->comment('状态');
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
        Schema::drop('timing_term');
    }
}
