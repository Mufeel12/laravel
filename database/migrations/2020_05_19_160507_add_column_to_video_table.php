<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('drm_sessions_count', 191)->nullable();
			$table->string('forensic_sessions_count', 191)->nullable();
			$table->string('visual_sessions_count', 191)->nullable();
			$table->string('translation_minutes', 191)->nullable();
			$table->string('caption_minutes', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('videos', function (Blueprint $table) {
            //
        });
    }
}
