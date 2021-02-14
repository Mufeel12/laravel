<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AccountSummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_summary', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->bigInteger('projects_count')->nullable();
            $table->bigInteger('videos_count')->nullable();
            $table->bigInteger('videos_views')->nullable();
            $table->bigInteger('views_total_watch_time')->nullable();
            $table->bigInteger('cost_this_month')->nullable();
            $table->bigInteger('cost_last_month')->nullable();
            $table->bigInteger('bandwidth_current_cycle')->nullable();
            $table->bigInteger('bandwidth_all_time')->nullable();
            $table->bigInteger('contact_size')->nullable();
            $table->bigInteger('compliance')->nullable();
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
        Schema::dropIfExists('account_summary');
    }
}
