<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionSourceToSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->boolean('subscription_source')->default(0);
            $table->string('job_title')->nullable();
            $table->string('organization')->nullable();
            $table->text('website')->nullable();
            $table->text('interests')->nullable();
            $table->text('location')->nullable();
            $table->longText('details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('subscription_source');
            $table->dropColumn('job_title');
            $table->dropColumn('organization');
            $table->dropColumn('website');
            $table->dropColumn('interests');
            $table->dropColumn('location');
            $table->dropColumn('details');
        });
    }
}
