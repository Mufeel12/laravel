<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerAccountSummaryVideoViewsCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER account_summary_video_views_create BEFORE INSERT ON statistics
                FOR EACH ROW
                    BEGIN
                        UPDATE account_summary SET videos_views = videos_views + 1
                        WHERE NEW.user_id = account_summary.user_id AND NEW.event = "video_view"
                        AND NEW.watch_session_id NOT IN (SELECT watch_session_id FROM statistics);
                        UPDATE account_summary SET views_total_watch_time = views_total_watch_time + (new.watch_end - new.watch_start)
                        WHERE NEW.user_id = account_summary.user_id AND NEW.event = "video_view";
                    END;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER account_summary_video_views_create');
    }
}
