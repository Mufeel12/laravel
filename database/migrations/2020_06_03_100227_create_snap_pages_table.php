<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSnapPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('snap_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->integer('video_id');
            $table->string('page_name');
            $table->string('title');
            $table->text('description');
            $table->string('logo', 255);
            $table->string('menu_1');
            $table->string('menu_link_1', 255);
            $table->string('menu_2');
            $table->string('menu_link_2', 255);
            $table->string('menu_3');
            $table->string('menu_link_3', 255);
            $table->string('button_text');
            $table->string('button_color');
            $table->string('name');
            $table->string('desination');
            $table->string('mobile_no');
            $table->string('profile_pic', 255);
            $table->string('facebook_link', 255);
            $table->string('twitter_link', 255);
            $table->string('instagram_link', 255);
            $table->string('linkedin_link', 255);
            $table->integer('status');
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
        Schema::dropIfExists('snap_pages');
    }
}
