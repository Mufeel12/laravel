<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriberDetailsColumnToSubscribersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('subscribers', function (Blueprint $table) {
			//$table->string('phone_number', 50)->nullable()->comment('Phone Number');
			$table->text('facebook_link')->nullable()->comment('Facebook Profile Link');
			$table->text('linked_in_link')->after('facebook_link')->nullable()->comment('LinkedIn Profile Link');
			$table->text('twitter_link')->after('linked_in_link')->nullable()->comment('Twitter Profile Link');
			$table->string('user_agent')->after('twitter_link')->nullable()->comment('User Agent Cookie');
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
			$table->dropColumn('phone_number');
			$table->dropColumn('facebook_link');
			$table->dropColumn('linked_in_link');
			$table->dropColumn('twitter_link');
			$table->dropColumn('user_agent');
		});
	}
}
