<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoPublishScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_publish_schedule', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('video_id'); 
            $table->boolean('is_schedule');
            $table->date('stream_start_date')->nullable();
            $table->integer('stream_start_hour')->nullable();
            $table->integer('stream_start_min')->nullable();
            $table->boolean('is_stream_start_text')->default(0);
            $table->text('stream_start_text')->nullable();
            $table->boolean('is_end_stream')->default(0);
            $table->date('stream_end_date')->nullable();
            $table->integer('stream_end_hour')->nullable();
            $table->integer('stream_end_min')->nullable();
            $table->boolean('is_stream_end_text')->default(0);
            $table->text('stream_end_text')->nullable(); 
            $table->boolean('is_action_button')->default(0);
            $table->string('button_text')->nullable();
            $table->text('button_link')->nullable();
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
        Schema::dropIfExists('video_publish_schedule');
    }
}
