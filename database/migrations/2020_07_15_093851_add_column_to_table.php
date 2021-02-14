<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('failed_payment_attempts', function (Blueprint $table) {
            $table->string('total');
            $table->string('plan_cost');
            $table->string('stripe_plan');
            $table->string('overage');
            $table->string('credit')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('failed_payment_attempts', function (Blueprint $table) {
            $table->dropColumn('total');
            $table->dropColumn('overage');
            $table->dropColumn('plan_cost');
            $table->dropColumn('stripe_plan');
            $table->dropColumn('credit')->nullable();
        });
    }
}
