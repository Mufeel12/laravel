<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentSecuiryLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_security_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['drm', 'forensic', 'visual']);
            $table->string('user_ip', 16);
            $table->string('session_id', 32);
            $table->string('video_id', 16);
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
        //
    }
}
