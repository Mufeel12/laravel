<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnsDefaultAccountSummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_summary', function (Blueprint $table) {
            $table->bigInteger('projects_count')->default('0')->change();
            $table->bigInteger('videos_count')->default('0')->change();
            $table->bigInteger('videos_views')->default('0')->change();
            $table->bigInteger('views_total_watch_time')->default('0')->change();
            $table->bigInteger('cost_this_month')->default('0')->change();
            $table->bigInteger('cost_last_month')->default('0')->change();
            $table->bigInteger('bandwidth_usage')->default('0')->change();
            $table->decimal('bandwidth_all_time', 15, 3)->default('0.000')->change();
            $table->bigInteger('contact_size')->default('0')->change();
            $table->bigInteger('compliance')->default('0')->change();
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
            $table->bigInteger('projects_count')->nullable()->change();
            $table->bigInteger('videos_count')->nullable()->change();
            $table->bigInteger('videos_views')->nullable()->change();
            $table->bigInteger('views_total_watch_time')->nullable()->change();
            $table->bigInteger('cost_this_month')->nullable()->change();
            $table->bigInteger('cost_last_month')->nullable()->change();
            $table->bigInteger('bandwidth_usage')->nullable()->change();
            $table->bigInteger('bandwidth_all_time')->nullable()->change();
            $table->bigInteger('contact_size')->nullable()->change();
            $table->bigInteger('compliance')->nullable()->change();
        });
    }
}
