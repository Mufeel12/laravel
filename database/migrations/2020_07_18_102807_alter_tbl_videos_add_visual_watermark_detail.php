<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblVideosAddVisualWatermarkDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_player_options', function (Blueprint $table) {
            $table->enum('visual_watermark_ip', ['false', 'true']);
            $table->enum('visual_watermark_timestamp', ['false', 'true']);
            $table->enum('visual_watermark_email', ['false', 'true']);
            $table->enum('visual_watermark_name', ['false', 'true']);

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
