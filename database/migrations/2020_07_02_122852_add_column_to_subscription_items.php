<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToSubscriptionItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->string('subscription_stripe_id')->nullable();
            $table->string('subscription_type')->nullable();
            $table->integer('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->dropColumn('subscription_stripe_id');
            $table->dropColumn('subscription_type');
            $table->dropColumn('user_id');
        });
    }
}
