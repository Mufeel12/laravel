<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBasescriptAndEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_basecodes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('video_id');
            $table->text('code');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('video_eventcodes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('basecode_id');
            $table->time('time');
            $table->text('code');
            $table->string('name');
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
        //
    }
}
