<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeWatchStartAndWatchEndNullableInStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->dropColumn('watch_start');
            $table->dropColumn('watch_end');
        });
        Schema::table('statistics', function (Blueprint $table) {
            $table->double('watch_start')->default(0);
            $table->double('watch_end')->default(0);
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
