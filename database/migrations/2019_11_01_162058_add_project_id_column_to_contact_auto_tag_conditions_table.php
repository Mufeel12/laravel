<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectIdColumnToContactAutoTagConditionsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('contact_auto_tag_conditions', function (Blueprint $table) {
      $table->integer('project_id')->after('specific_video_type')->default(0)->index();
      $table->text('specific_videos')->after('project_id');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('contact_auto_tag_conditions', function (Blueprint $table) {
      $table->dropColumn('project_id');
      $table->dropColumn('specific_videos');
    });
  }
}
