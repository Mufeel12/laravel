<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoThumbnailScrumbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_thumbnail_scrumbs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path');
            $table->string('url');
            $table->string('thumbnail_scrumb');
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
        Schema::dropIfExists('video_thumbnail_scrumbs');
    }
}
