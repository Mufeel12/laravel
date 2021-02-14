<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('body');
            $table->morphs('commentable');
            $table->morphs('creator');
            $table->integer('video_time');
            $table->integer('is_resolved');
            \Kalnoy\Nestedset\NestedSet::columns($table);
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
        Schema::dropIfExists('comments');
        \Kalnoy\Nestedset\NestedSet::dropColumns('comments');

    }
}
