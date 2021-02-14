<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerVideosViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER user_video_views_create_acc');
        DB::unprepared('DROP TRIGGER user_video_views_update_acc');
        DB::unprepared('
        CREATE TRIGGER user_video_views_create_acc AFTER INSERT ON statistics_summary
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM videos WHERE id = NEW.video_id;
                        SELECT SUM((SELECT SUM(ss.video_views) FROM statistics_summary AS ss WHERE ss.video_id = v.id)) INTO @video_views
                        FROM videos AS v
                        WHERE owner = @user_id;

                        UPDATE account_summary SET videos_views = @video_views WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_video_views_update_acc AFTER UPDATE ON statistics_summary
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM videos WHERE id = NEW.video_id;
                        SELECT SUM((SELECT SUM(ss.video_views) FROM statistics_summary AS ss WHERE ss.video_id = v.id)) INTO @video_views
                        FROM videos AS v
                        WHERE owner = @user_id;

                        UPDATE account_summary SET videos_views = @video_views WHERE user_id = @user_id;
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
        DB::unprepared('DROP TRIGGER user_video_views_create_acc');
        DB::unprepared('DROP TRIGGER user_video_views_update_acc');
        DB::unprepared('
        CREATE TRIGGER user_video_views_create_acc AFTER INSERT ON statistics_summary
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM videos WHERE id = NEW.video_id;
                        SELECT SUM((SELECT SUM(ss.video_views) FROM statistics_summary AS ss WHERE ss.video_id = v.id)) INTO @video_views
                        FROM videos AS v
                        WHERE owner = @user_id;

                        UPDATE account_summary SET videos_views = @video_views WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_video_views_update_acc AFTER UPDATE ON statistics_summary
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM videos WHERE id = NEW.video_id;
                        SELECT SUM((SELECT SUM(ss.video_views) FROM statistics_summary AS ss WHERE ss.video_id = v.id)) INTO @video_views
                        FROM videos AS v
                        WHERE owner = @user_id;

                        UPDATE account_summary SET videos_views = @video_views WHERE user_id = @user_id;
                    END;
        ');
    }
}
