<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserTeamProjectToSubscriber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscribers', function($table) {
            $table->dropColumn('cta_element_id');
            $table->dropColumn('name');
            $table->integer('project_id')->index();
            $table->integer('team_id')->index();
            $table->integer('video_id')->nullable()->index();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscribers', function($table) {
            $table->integer('cta_element_id');
            $table->string('name');
            $table->dropColumn('project_id');
            $table->dropColumn('team_id');
            $table->dropColumn('video_id')->nullable();
            $table->dropColumn('firstname')->nullable();
            $table->dropColumn('lastname')->nullable();
        });
    }
}
