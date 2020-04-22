<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamQuestionsLicence extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_questions_licence', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exam_questions_id')->comment('题目编号');
            $table->integer('licence_type_id')->comment('驾照类型');
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
        Schema::drop('exam_questions_licence');
    }
}
