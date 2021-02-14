<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_schedule', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->enum('pay_by',['user','manual','waive'])->nullable();
            $table->enum('schedule_type',['cycle_end','specific_date'])->nullable();
            $table->string('sub_schd_id');
            $table->string('current_sub_id');
            $table->string('current_plan_id');
            $table->string('upcoming_plan_id');
            $table->string('current_sub_type');
            $table->string('upcoming_sub_type');
            $table->string('sub_start_timestamp');
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_schedule');
    }
}
