<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexBunnycdnBandwidthRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bunnycdn_bandwidth_records', function (Blueprint $table) {
            $table->index('video_id');
        });
        Schema::table('bunnycdn_bandwidth_records', function (Blueprint $table) {
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIndex('video_id');
        Schema::dropIndex('timestamp');
    }
}
