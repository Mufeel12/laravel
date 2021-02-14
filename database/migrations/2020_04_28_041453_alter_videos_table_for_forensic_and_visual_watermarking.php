<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVideosTableForForensicAndVisualWatermarking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->boolean('forensic_watermarking')->after('drm_protection')->default(false);
            $table->boolean('visual_watermarking')->after('drm_protection')->default(false);
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
            $table->dropColumn('forensic_watermarking');
            $table->dropColumn('visual_watermarking');
        });
    }
}
