<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranscodingParametersColumnToVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('thumbnail_scrumb')->nullable();
            $table->text('thumbnails');

            $table->text('files');

            $table->integer('transcoding_progress');
            $table->string('transcoding_size_source');
            $table->string('transcoding_size_out');
            $table->string('transcoding_price');
            $table->string('transcoding_badnwidth');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('videos', function (Blueprint $table) {
            //
        });
    }
}
