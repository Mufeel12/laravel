<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailedPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_payment_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('invoice_id');
            $table->string('stripe_id');
            $table->date('cancel_date');
            $table->integer('attempt');
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
        Schema::dropIfExists('failed_payment_attempts');
    }
}
