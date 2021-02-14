<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnCurrentCycleAccountSummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_summary', function (Blueprint $table) {
            $table->dropColumn('bandwidth_current_cycle');
            $table->bigInteger('bandwidth_usage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_summary', function (Blueprint $table) {
            $table->dropColumn('bandwidth_usage');
            $table->bigInteger('bandwidth_current_cycle');
        });
    }
}
