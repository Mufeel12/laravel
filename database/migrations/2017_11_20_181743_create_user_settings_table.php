<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('user_image');
            $table->string('storage_space');
            $table->string('timezone')->nullable();
            $table->string('date_format')->nullable();
            $table->string('currency_format')->nullable();
            $table->string('sec_q_1')->nullable();
            $table->string('sec_q_2')->nullable();
            $table->string('sec_q_3')->nullable();
            $table->string('sec_a_1')->nullable();
            $table->string('sec_a_2')->nullable();
            $table->string('sec_a_3')->nullable();
            $table->string('logo')->nullable();
            $table->enum('subscriber_storage', ['active', 'deactivated']);
            $table->string('aweber')->nullable();
            $table->string('constantcontact')->nullable();
            $table->string('icontact')->nullable();
            $table->string('mailchimp')->nullable();
            $table->string('infusionsoft')->nullable();
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
        Schema::dropIfExists('settings');
    }
}
