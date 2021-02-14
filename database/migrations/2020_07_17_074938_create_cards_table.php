<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('fingerprint');
            $table->string('source_id');
            $table->string('exp_year');
            $table->string('exp_month');
            $table->string('card_brand');
            $table->string('card_last_four');
            $table->string('paymeny_method')->default('stripe');
            $table->string('currency')->default('USD');
            $table->enum('default_card',['yes', 'no'])->default('no');
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
        Schema::dropIfExists('cards');
    }
}
