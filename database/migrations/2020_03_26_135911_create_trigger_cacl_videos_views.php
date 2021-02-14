<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerCaclVideosViews extends Migration
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
        Schema::table('videos', function (Blueprint $table) {
            $table->integer('views')->default(0);
        });

        DB::unprepared('
        CREATE TRIGGER videos_views_create BEFORE INSERT ON statistics
                FOR EACH ROW
                    BEGIN
                        UPDATE videos SET views = views + 1 
                        WHERE NEW.video_id = videos.id AND NEW.event = "video_view"
                        AND NEW.watch_session_id NOT IN (SELECT watch_session_id FROM statistics);
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
        //
    }
}
