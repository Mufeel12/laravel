<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerUserVideoAcc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER user_video_create_acc AFTER INSERT ON videos
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM videos WHERE id = NEW.id;
                        SELECT count(*) INTO @videos_count FROM videos AS v
                        WHERE owner = @user_id;

                        UPDATE account_summary SET videos_count = @videos_count WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_video_update_acc AFTER UPDATE ON videos
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM videos WHERE id = NEW.id;
                        SELECT count(*) INTO @videos_count FROM videos AS v
                        WHERE owner = @user_id;

                        UPDATE account_summary SET videos_count = @videos_count WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_video_delete_acc AFTER DELETE ON videos
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM videos WHERE id = NEW.id;
                        SELECT count(*) INTO @videos_count FROM videos AS v
                        WHERE owner = @user_id;

                        UPDATE account_summary SET videos_count = @videos_count WHERE user_id = @user_id;
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
        DB::unprepared('DROP TRIGGER user_video_create_acc');
        DB::unprepared('DROP TRIGGER user_video_update_acc');
        DB::unprepared('DROP TRIGGER user_video_delete_acc');
    }
}
