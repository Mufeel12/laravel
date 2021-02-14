<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('videos', function (Blueprint $table) {
			$table->increments('id');
			$table->string('video_id', 25)->index();
			$table->string('title');
			$table->string('project')->index();
			$table->string('owner')->index();

			$table->integer('team')->index();
			$table->string('filename');
			$table->string('thumbnail');
			$table->string('path');
			$table->integer('duration');
			$table->string('duration_formatted');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('videos');
	}
}
