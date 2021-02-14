<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalFieldsToStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->dropColumn('video_total_watch_time');
            $table->double('watch_start');
            $table->double('watch_end');
            $table->string('city')->after('country_name');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->integer('video_total_watch_time');
            $table->dropColumn('watch_start');
            $table->dropColumn('watch_end');
            $table->dropColumn('city');
	    });
    }
}
