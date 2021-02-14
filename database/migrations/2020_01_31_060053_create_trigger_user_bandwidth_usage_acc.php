<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerUserBandwidthUsageAcc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE TRIGGER user_bandwidth_usage_acc AFTER INSERT ON bunnycdn_bandwidth_records
                FOR EACH ROW
                BEGIN
                    SELECT owner INTO @user_id FROM videos WHERE video_id = NEW.video_id;
                    SET @date_start := DATE_ADD(CURDATE(), INTERVAL -DAY(CURDATE())+1 DAY);

                    SELECT SUM(bbr.bytes_sent) AS total INTO @bandwidth_usage FROM videos AS v
                        LEFT JOIN bunnycdn_bandwidth_records bbr ON v.video_id = bbr.video_id
                    WHERE v.owner = @user_id AND bbr.created_at >= @date_start;

                    SET @bandwidth_usage = IF(@bandwidth_usage IS NULL, 0, @bandwidth_usage);

                    UPDATE users SET bandwidth_usage = @bandwidth_usage WHERE id = @user_id;
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
        DB::unprepared('DROP TRIGGER user_bandwidth_usage_acc');
    }
}
