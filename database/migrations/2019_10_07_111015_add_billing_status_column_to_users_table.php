<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingStatusColumnToUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->enum('billing_status', ['Active', 'Inactive', 'Trial', 'Expired', 'Cancelled', 'Failed', 'VerifyRequired'])
				->after('last_read_announcements_at')
				->default('Active');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('billing_status');
		});
	}
}
