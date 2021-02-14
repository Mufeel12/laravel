<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBunnycdnBandwidthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bunnycdn_bandwidth_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('video_id');
            $table->string('file_name');
            $table->string('timespan', 255);
            $table->integer('timestamp')->length(200)->unsigned();
            $table->integer('bytes_sent')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bunnycdn_bandwidth_records');
    }
}
