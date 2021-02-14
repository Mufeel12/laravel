<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemoteIpAddressAndUniqueLogToBunnycdnBandwidthRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bunnycdn_bandwidth_records', function (Blueprint $table) {
            $table->string('remote_ip_address')->after('bytes_sent')->nullable();
            $table->string('unique_log')->after('video_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bunnycdn_bandwidth_records', function (Blueprint $table) {
            $table->dropColumn('remote_ip_address');
            $table->dropColumn('unique_log');
        });
    }
}
