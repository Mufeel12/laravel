<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSharedSnapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shared_snaps', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id');
            $table->integer('video_id')->nullable();
            $table->integer('snap_label_id')->nullable();
            $table->string('creator_name', 50)->nullable();
            $table->string('creator_email', 50)->nullable();
            $table->integer('completed')->comment('0 => No, 1 => Yes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shared_snaps');
    }
}
