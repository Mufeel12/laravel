<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStageSettingsColumnToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('settings', function (Blueprint $table) {
		    $table->enum('stage_visibility', ['publish', 'hidden'])->after('bandwidth_exceeded')->default('publish')->comment('Stage Setting');
		    $table->boolean('show_email_capture')->after('stage_visibility')->default(1)->comment('Stage Setting');
		    $table->boolean('notify_to_subscribers')->after('show_email_capture')->default(1)->comment('Stage Setting');
		    $table->boolean('auto_tag_email_list')->after('notify_to_subscribers')->default(1)->comment('Stage Setting');
		    $table->string('email_list_id')->after('auto_tag_email_list')->comment('Stage Setting');
		    $table->text('stage_tags')->after('email_list_id')->nullable()->comment('Stage Setting');
		    $table->string('stage_name')->after('stage_tags')->nullable()->comment('Stage Setting');
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
		    $table->dropColumn('stage_visibility');
		    $table->dropColumn('show_email_capture');
		    $table->dropColumn('notify_to_subscribers');
		    $table->dropColumn('auto_tag_email_list');
		    $table->dropColumn('email_list_id');
		    $table->dropColumn('stage_tags');
		    $table->dropColumn('stage_name');
	    });
    }
}
