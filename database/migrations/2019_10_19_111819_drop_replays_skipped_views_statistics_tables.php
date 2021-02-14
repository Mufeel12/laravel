<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropReplaysSkippedViewsStatisticsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('statistics_engagement_replays')) {
            Schema::dropIfExists('statistics_engagement_replays');
            Schema::dropIfExists('statistics_engagement_skipped');
            Schema::dropIfExists('statistics_engagement_views');
        }
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
