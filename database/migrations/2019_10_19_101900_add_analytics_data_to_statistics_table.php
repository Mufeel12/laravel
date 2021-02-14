<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAnalyticsDataToStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics', function($table) {
            $table->dropColumn('object_type');
            $table->dropColumn('object_id');
            $table->dropColumn('action');
            $table->integer('video_id')->index();
            $table->integer('project_id')->index();
            $table->integer('user_id')->index();
            $table->integer('team_id')->index();
            $table->string('event')->index(); // video_view, video_replay, video_skip_ahead, email_capture, click, skip
            $table->string('cookie');
            $table->string('unique_ref')->index();
            $table->mediumtext('agents');
            $table->string('kind');
            $table->string('model');
            $table->string('platform');
            $table->string('platform_version');
            $table->enum('is_mobile', [1, 0])->index();
            $table->string('browser');
            $table->string('domain');
            $table->double('latitude');
            $table->double('longitude');
            $table->string('country_code', 2);
            $table->string('country_name');
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
            $table->string('object_type');
            $table->integer('object_id');
            $table->string('action');
            $table->dropColumn('object_type');
            $table->dropColumn('object_id');
            $table->dropColumn('action');
            $table->dropColumn('video_id');
            $table->dropColumn('project_id');
            $table->dropColumn('user_id');
            $table->dropColumn('team_id');
            $table->dropColumn('event');
            $table->dropColumn('cookie');
            $table->dropColumn('unique_ref');
            $table->dropColumn('agents');
            $table->dropColumn('kind');
            $table->dropColumn('model');
            $table->dropColumn('platform');
            $table->dropColumn('platform_version');
            $table->dropColumn('is_mobile');
            $table->dropColumn('browser');
            $table->dropColumn('domain');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('country_code', 2);
            $table->dropColumn('country_name');
        });
    }
}
