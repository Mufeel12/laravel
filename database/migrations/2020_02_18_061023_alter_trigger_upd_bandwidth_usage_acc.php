<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTriggerUpdBandwidthUsageAcc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER user_bandwidth_usage_acc');
        DB::unprepared('
            CREATE TRIGGER user_bandwidth_usage_acc AFTER INSERT ON bunnycdn_bandwidth_records
                FOR EACH ROW
                BEGIN
                    SELECT owner INTO @user_id FROM videos WHERE video_id = NEW.video_id;
                    SELECT DAY(ends_at) INTO @day_start FROM subscriptions WHERE user_id = @user_id;

                    SET @today = DAY(CURDATE());
                    SET @date_start = IF(
                        @today < @day_start,
                        DATE_SUB(CURDATE(), INTERVAL @today + @day_start DAY),
                        DATE_SUB(CURDATE(), INTERVAL @today - @day_start DAY)
                    );

                    SELECT SUM(bbr.bytes_sent) AS total INTO @bandwidth_usage FROM videos AS v
                        LEFT JOIN bunnycdn_bandwidth_records bbr ON v.video_id = bbr.video_id
                    WHERE v.owner = @user_id AND bbr.created_at >= @date_start;

                    SELECT
                        bandwidth_all_time, bandwidth_usage
                    INTO
                        @old_bandwidth_all_time, @old_bandiwdth_usage
                    FROM
                        account_summary
                    WHERE
                        user_id = @user_id;

                    SET @bandwidth_usage_diff = @bandwidth_usage - @old_bandwidth_usage;
                    SET @bandwidth_usage = IF(@bandwidth_usage IS NULL, 0, @bandwidth_usage);
                    SET @bandwidth_all_time_default = IF(
                        @old_bandwidth_all_time > 0,
                        @old_bandwidth_all_time,
                        @bandwidth_usage
                    );
                    SET @bandwidth_all_time = IF(
                        @bandwidth_usage_diff > 0,
                        @old_bandwidth_all_time + @bandwidth_usage_diff,
                        @bandwidth_all_time_default
                    );

                    UPDATE account_summary
                    SET
                        bandwidth_usage = @bandwidth_usage,
                        bandwidth_all_time = (@bandwidth_all_time / 1024 / 1024 / 1024)
                    WHERE user_id = @user_id;
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

                    UPDATE account_summary SET bandwidth_all_time = @bandwidth_usage WHERE user_id = @user_id;
                END;
        ');
    }
}
