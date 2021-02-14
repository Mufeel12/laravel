<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('activity_log', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id');
			$table->enum('activity_type', array('login', 'logout', 'create_project', 'del_project', 'create_collaborate_project', 'created_experiment', 'paused_experiment', 'del_experiment', 'video_upload', 'video_del', 'paid_invoice', 'cancel_subscription', 'payment_attempt', 'changed_subscription', 'subuser_added', 'subuser_del','remove_collaborate_project'));
			$table->string('ip');
			$table->string('method')->nullable();
			$table->text('subject');
			$table->dateTime('created_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('activity_log');
	}
}
