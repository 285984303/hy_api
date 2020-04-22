<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laravel_sms', function (Blueprint $table) {
            $table->increments('id');

            //to:用于存储手机号
            $table->string('to')->default('')->comment('用于存储手机号');

            //temp_id:存储模板标记，用于存储任何第三方服务商提供的短信模板标记/id
            $table->string('temp_id')->default('')->comment('存储模板标记');

            //data:模板短信的模板数据，建议json格式
            $table->string('data')->default('')->comment('模板短信的模板数据');

            //content:内容
            $table->string('content')->default('')->comment('内容');

            //voice_code:语言验证码code
            $table->string('voice_code')->default('')->comment('语言验证码code');

            //发送失败次数
            $table->mediumInteger('fail_times')->default(0)->comment('发送失败次数');

            //最后一次发送失败时间
            $table->integer('last_fail_time')->unsigned()->default(0)->comment('最后一次发送失败时间');

            //发送成功时的时间
            $table->integer('sent_time')->unsigned()->default(0)->comment('发送成功时的时间');

            //代理器使用日志，记录每个代理器的发送状态，可用于排错
            $table->text('result_info')->nullable()->comment('代理器使用日志');

            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';

            //说明
            //1：temp_id和data用于发送模板短信。
            //2：content用于直接发送短信内容，不使用模板。
            //3：voice_code用于存储语言验证码code。
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laravel_sms');
    }
}
