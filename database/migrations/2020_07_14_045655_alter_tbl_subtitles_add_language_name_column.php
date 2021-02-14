<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblSubtitlesAddLanguageNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_subtitles', function (Blueprint $table) {
            $table->string('lang_name')->nullable();
        });
        Schema::table('translated_subtitles', function (Blueprint $table) {
            $table->string('lang_name')->nullable();
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
