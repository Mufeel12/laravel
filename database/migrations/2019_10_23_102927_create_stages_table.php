<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStagesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stages', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			
			$table->string('about_title')->nullable();
			$table->longText('about_description')->nullable();
			$table->text('cover_image')->nullable();
			$table->boolean('first_visit')->default(1);
			$table->boolean('show_website')->default(1);
			$table->string('website')->nullable();
			$table->boolean('show_phone_number')->default(1);
			$table->string('phone_number')->nullable();
			$table->boolean('show_email')->default(1);
			$table->string('email')->nullable();
			$table->boolean('show_facebook')->default(1);
			$table->string('facebook')->nullable();
			$table->boolean('show_instagram')->default(1);
			$table->string('instagram')->nullable();
			$table->boolean('show_twitter')->default(1);
			$table->string('twitter')->nullable();
			
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
		Schema::dropIfExists('stages');
	}
}
