<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamQuestionsChapter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_questions_chapter', function (Blueprint $table) {
            $table->increments('id');
            $table->string('chapter',30)->comment('章节');
            $table->enum('subject',[1,2,3,4])->comment('科目');
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
        Schema::drop('exam_questions_chapter');
    }
}
