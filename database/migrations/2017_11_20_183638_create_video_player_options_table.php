<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoPlayerOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_player_options', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('video_id')->unsigned();
            $table->enum('control_visibility', ['on_hover', 'always', 'hide', 'hide_progress']);
            $table->enum('autoplay', ['true', 'false']);
            $table->enum('speed_control', ['true', 'false']);
            $table->enum('volume_control', ['true', 'false']);
            $table->enum('share_control', ['true', 'false']);
            $table->enum('fullscreen_control', ['true', 'false']);
            $table->enum('playback', ['true', 'false']); // what is playback?
            $table->enum('branding_active', ['true', 'false']);
            $table->integer('thumbnail_video_start')->nullable();
            $table->integer('thumbnail_video_end')->nullable();
            $table->enum('text_overlay', ['true', 'false']);
            $table->text('text_overlay_text')->nullable();

            $table->string('color');

            $table->enum('thumbnail_type', ['image', 'video']);
            //$table->text('thumbnail_image');

            $table->enum('allow_download', ['true', 'false']);

            // Privacy viewing permissions
            $table->enum('permissions', ['inherit', 'everyone', 'collaborators', 'password', 'private_link']);
            $table->string('password')->nullable();
            $table->string('password_button_text')->nullable();
            $table->text('private_link')->nullable();
            $table->enum('embed_settings', ['inherit', 'any', 'none', 'whitelisted_domains']);
            $table->text('whitelisted_domains')->nullable();
            $table->enum('commenting_permissions', ['inherit', 'everyone', 'collaborators']);

            $table->text('redirect_url')->nullable();
            $table->text('pixel_tracking')->nullable();

            // interaction before video
            $table->enum('interaction_before_email_capture', ['true', 'false']);
            $table->enum('interaction_before_email_capture_type', ['full', 'minimized']);
            $table->enum('interaction_before_email_capture_firstname', ['true', 'false']);
            $table->enum('interaction_before_email_capture_lastname', ['true', 'false']);
            $table->enum('interaction_before_email_capture_phone_number', ['true', 'false']);
            $table->enum('interaction_before_email_capture_allow_skip', ['true', 'false']);
            $table->text('interaction_before_email_capture_upper_text')->nullable();
            $table->text('interaction_before_email_capture_lower_text')->nullable();
            $table->string('interaction_before_email_capture_button_text')->nullable();
            $table->text('interaction_before_email_capture_email_list')->nullable();
            $table->text('interaction_before_email_capture_email_tags')->nullable();

            // interaction during video
            $table->integer('interaction_during_time');
            $table->enum('interaction_during_active', ['true', 'false']);
            $table->enum('interaction_during_type', ['text', 'image', 'html_code']);
            $table->enum('interaction_during_allow_skip', ['true', 'false']);
            $table->text('interaction_during_text')->nullable();
            $table->text('interaction_during_image')->nullable();
            $table->text('interaction_during_link_url')->nullable();
            $table->text('interaction_during_html_code')->nullable();

            $table->enum('interaction_during_email_capture', ['true', 'false']);
            $table->integer('interaction_during_email_capture_time');
            $table->enum('interaction_during_email_capture_type', ['full', 'minimized']);
            $table->enum('interaction_during_email_capture_firstname', ['true', 'false']);
            $table->enum('interaction_during_email_capture_lastname', ['true', 'false']);
            $table->enum('interaction_during_email_capture_phone_number', ['true', 'false']);
            $table->enum('interaction_during_email_capture_allow_skip', ['true', 'false']);
            $table->text('interaction_during_email_capture_upper_text')->nullable();
            $table->text('interaction_during_email_capture_lower_text')->nullable();
            $table->string('interaction_during_email_capture_button_text')->nullable();
            $table->text('interaction_during_email_capture_email_list')->nullable();
            $table->text('interaction_during_email_capture_email_tags')->nullable();

            // interaction after video
            $table->enum('interaction_after_type', ['more_videos', 'email_capture', 'call_to_action', 'loop', 'show_last_frame', 'show_thumbnail', 'redirect']);
            $table->text('interaction_after_more_videos_list')->nullable();
            $table->text('interaction_after_more_videos_text')->nullable();
            $table->enum('interaction_after_cta_type', ['html_code', 'image', 'text']);
            $table->text('interaction_after_cta_html_code')->nullable();
            $table->text('interaction_after_cta_image')->nullable();
            $table->text('interaction_after_cta_text')->nullable();
            $table->text('interaction_after_cta_link_url')->nullable();

            $table->enum('interaction_after_email_capture', ['true', 'false']);
            $table->enum('interaction_after_email_capture_type', ['full', 'minimized']);
            $table->enum('interaction_after_email_capture_firstname', ['true', 'false']);
            $table->enum('interaction_after_email_capture_lastname', ['true', 'false']);
            $table->enum('interaction_after_email_capture_phone_number', ['true', 'false']);
            $table->enum('interaction_after_email_capture_allow_skip', ['true', 'false']);
            $table->text('interaction_after_email_capture_upper_text')->nullable();
            $table->text('interaction_after_email_capture_lower_text')->nullable();
            $table->string('interaction_after_email_capture_button_text')->nullable();
            $table->text('interaction_after_email_capture_email_list')->nullable();
            $table->text('interaction_after_email_capture_email_tags')->nullable();

            /*
              // what is play bar?
              ***** Play bar
             */
            $table->timestamps();
        });

        Schema::table('video_player_options', function(Blueprint $table) {
            $table->foreign('video_id')->references('id')->on('videos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_player_options');
    }
}
