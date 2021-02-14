<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationSettingsColumnToSettingsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('settings', function (Blueprint $table) {
			$table->boolean('comments_my_video')->after('zip_code')->default(1)->comment('Notification Setting');
			$table->boolean('shares_my_video')->after('comments_my_video')->default(1)->comment('Notification Setting');
			$table->boolean('download_my_video')->after('shares_my_video')->default(1)->comment('Notification Setting');
			$table->boolean('email_captured')->after('download_my_video')->default(1)->comment('Notification Setting');
			$table->boolean('bandwidth_exceeded')->after('email_captured')->default(1)->comment('Notification Setting');
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
			$table->dropColumn('comments_my_video');
			$table->dropColumn('shares_my_video');
			$table->dropColumn('download_my_video');
			$table->dropColumn('email_captured');
			$table->dropColumn('bandwidth_exceeded');
		});
	}
}
