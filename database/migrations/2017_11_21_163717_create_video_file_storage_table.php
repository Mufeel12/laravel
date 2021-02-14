<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoFileStorageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_file_storage', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('video_id');
            $table->integer('size');
            $table->text('quality_file_sizes');
            $table->integer('total_size');
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
        Schema::dropIfExists('video_file_storage');
    }
}
