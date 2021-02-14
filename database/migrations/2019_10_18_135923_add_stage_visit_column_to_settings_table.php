<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStageVisitColumnToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('settings', function (Blueprint $table) {
		    $table->boolean('stage_visit')->default(0)->after('stage_name')->comment('First Stage Visit');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('settings', function (Blueprint $table) {
		    $table->dropColumn('stage_visit');
	    });
    }
}
