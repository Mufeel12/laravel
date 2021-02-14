<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddViewsReplaySkipEmailClickColumsToStatisticsSummaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics_summary', function($table) {
            $table->dropColumn('object_type');
            $table->dropColumn('object_id');
            $table->dropColumn('action');
            $table->integer('video_id');
            $table->integer('project_id');
            $table->integer('team_id');
            $table->integer('video_total_watch_time')->nullable();
            $table->integer('video_views');
            $table->integer('video_skipped_aheads');
            $table->integer('skipped');
            $table->integer('clicks');
            $table->integer('email_captures');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistics_summary', function($table) {
            $table->string('object_type');
            $table->string('object_id');
            $table->string('action');
            $table->dropColumn('video_id');
            $table->dropColumn('project_id');
            $table->dropColumn('team_id');
            $table->dropColumn('video_total_watch_time');
            $table->dropColumn('video_views');
            $table->dropColumn('video_skipped_aheads');
            $table->dropColumn('skipped');
            $table->dropColumn('clicks');
            $table->dropColumn('email_captures');
        });
    }
}
