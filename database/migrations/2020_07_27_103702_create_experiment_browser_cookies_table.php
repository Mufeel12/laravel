<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExperimentBrowserCookiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experiment_browser_cookies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('experiment_type');
            $table->integer('experiment_id');
            $table->string('cookie');
            $table->integer('thumbnail_video_id');
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
        Schema::dropIfExists('experiment_browser_cookies');
    }
}
