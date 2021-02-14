<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveVideoCountAndVideoViewsCountFromProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
		    $table->dropColumn('video_count');
		    $table->dropColumn('video_views_count');
            $table->dropColumn('thumbnails');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
		    $table->integer('video_count');
		    $table->integer('video_views_count');
            $table->string('thubmnais');
	    });
    }
}
