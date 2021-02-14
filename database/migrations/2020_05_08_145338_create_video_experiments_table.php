<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoExperimentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_experiments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('active');
            $table->string('title');
            $table->string('project_id');
            $table->json('goals');
            $table->integer('video_id_a');
            $table->integer('video_id_b');
            $table->integer('duration');
            $table->integer('action');
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
        Schema::dropIfExists('video_experiments');
    }
}
