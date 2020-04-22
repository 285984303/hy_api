<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainingAudit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_audit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('signcheck_id',30)->default('')->comment('签章主表ID');
            $table->integer('user_id')->comment('用户Id');
            $table->integer('subject_1_hour')->default(0)->comment('科目一学时');
            $table->integer('subject_2_hour')->default(0)->comment('科目二学时');
            $table->integer('subject_3_hour')->default(0)->comment('科目三学时');
            $table->integer('subject_4_hour')->default(0)->comment('科目四学时');
            $table->integer('today_hour')->default(0)->comment('今日新增学时');
            $table->integer('subject')->nullable()->comment('单前科目');
            $table->integer('admin_id')->nullable()->comment('送审人');
            $table->string('audit_opinion')->default('')->comment('送审意见');
            $table->timestamp('audit_date')->nullable()->comment('审批日期');
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
        Schema::drop('training_audit');
    }
}
