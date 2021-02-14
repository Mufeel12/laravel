<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateContactAutoTagConditionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contact_auto_tag_conditions', function (Blueprint $table) {
			$table->bigIncrements('id');
			
			$table->bigInteger('auto_tag_id')->unsigned();
			$table->foreign('auto_tag_id')->references('id')->on('contact_auto_tags')->onDelete('cascade');
			
			$table->enum('watched', ['watched', 'not_watch', 'clicked_link', 'not_click', 'subscribed', 'not_subscribe']);
			$table->enum('video_type', ['specific', 'any']);
			$table->tinyInteger('specific_video_type');
			$table->enum('timeline_type', ['1', '7', '30', '90', '365', 'last_year', 'year', 'any_time', 'last_month', 'month', 'custom']);
			$table->date('start_date');
			$table->date('end_date');
			$table->enum('combination', ['OR', 'AND']);
			
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
			$table->engine = 'InnoDB';
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('contact_auto_tag_conditions');
	}
}
