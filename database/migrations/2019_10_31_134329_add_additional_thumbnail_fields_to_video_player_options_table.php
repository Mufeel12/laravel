<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalThumbnailFieldsToVideoPlayerOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_player_options', function (Blueprint $table) {
            $table->dropColumn('thumbnail_video_start');
            $table->dropColumn('thumbnail_video_end');
            $table->string('thumbnail_image_url');
            $table->string('thumbnail_video_url');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_player_options', function (Blueprint $table) {
		    $table->integer('thumbnail_video_start');
            $table->integer('thumbnail_video_end');
		    $table->dropColumn('thumbnail_type');
            $table->dropColumn('thumbnail_image_url');
            $table->dropColumn('thumbnail_video_url');
	    });

    }
}
