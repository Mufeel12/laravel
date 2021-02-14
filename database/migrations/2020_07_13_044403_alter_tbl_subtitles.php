<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblSubtitles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_subtitles', function (Blueprint $table) {
            $table->string('url')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0->pending 1->success 2->fail');
        });

        Schema::table('translated_subtitles', function (Blueprint $table) {
            $table->string('url')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0->pending 1->success 2->fail');
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
