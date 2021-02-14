<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDashboardToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('dashboard_settings')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uses_two_factor_auth');
            $table->dropColumn('authy_id');
            $table->dropColumn('two_factor_reset_code');
            $table->dropColumn('last_read_announcements_at');
            $table->dropColumn('braintree_id');
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
            $table->dropColumn('dashboard_settings');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('uses_two_factor_auth');
            $table->integer('authy_id');
            $table->integer('two_factor_reset_code');
            $table->integer('last_read_announcements_at');
        });
    }
}
