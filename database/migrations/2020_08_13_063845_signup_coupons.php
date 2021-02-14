<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SignupCoupons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signup_coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('coupon_id');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('plan_id');
            $table->integer('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('signup_coupons');
    }
}
