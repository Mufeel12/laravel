<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventSpecificAnalyticsDataToStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics', function($table) {
            $table->integer('video_total_watch_time')->nullable();
            $table->integer('event_offset_time')->nullable();
            $table->string('event_interaction_group')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistics', function($table) {
            $table->dropColumn('video_total_watch_time');
            $table->dropColumn('event_offset_time');
            $table->dropColumn('event_interaction_group');
        });
    }
}
