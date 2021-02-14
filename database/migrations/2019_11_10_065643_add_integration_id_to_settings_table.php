<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIntegrationIdToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('sec_q_1');
            $table->dropColumn('sec_q_2');
            $table->dropColumn('sec_q_3');
            $table->dropColumn('sec_a_1');
            $table->dropColumn('sec_a_2');
            $table->dropColumn('sec_a_3');
            $table->dropColumn('subscriber_storage');
            $table->dropColumn('aweber');
            $table->dropColumn('constantcontact');
            $table->dropColumn('icontact');
            $table->dropColumn('mailchimp');
            $table->dropColumn('infusionsoft');
            $table->dropColumn('date_format');
            $table->bigInteger('integration_id')->after('email_list_id')->index();
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
            $table->integer('sec_q_1');
            $table->integer('sec_q_2');
            $table->integer('sec_q_3');
            $table->integer('sec_a_1');
            $table->integer('sec_a_2');
            $table->integer('sec_a_3');
            $table->integer('subscriber_storage');
            $table->integer('aweber');
            $table->integer('constantcontact');
            $table->integer('icontact');
            $table->integer('mailchimp');
            $table->integer('infusionsoft');
            $table->integer('date_format');
            $table->dropColumn('integration_service_id');
        });
    }
}
