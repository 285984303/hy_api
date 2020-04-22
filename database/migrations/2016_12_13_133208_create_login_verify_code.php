<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoginVerifyCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_verify_code', function (Blueprint $table) {
            $table->increments('id');
            $table->string('to',20)->comment('短信手机号');
            $table->string('code',10)->comment('验证码');
            $table->string('type')->default('login')->comment('类型 登录验证码/修改密码验证码');
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
        Schema::drop('login_verify_code');
    }
}
