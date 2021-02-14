<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('videos', function (Blueprint $table) {
			$table->string('total_size', 191)->nullable();
			$table->string('drm', 191)->nullable();
			$table->string('drm_licenses_issued', 191)->nullable();
			$table->string('drm_devices', 191)->nullable();
			$table->string('server_cost', 191)->nullable();
			$table->string('server_cost_max', 191)->nullable();
			$table->string('transfer_cost', 191)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('videos', function (Blueprint $table) {
			//
		});
	}
}
