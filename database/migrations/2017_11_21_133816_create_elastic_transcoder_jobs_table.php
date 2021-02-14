<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateElasticTranscoderJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elastic_transcoder_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('jobId');
            $table->string('state');
            $table->string('version');
            $table->string('pipelineId');
            $table->string('input_key');
            $table->integer('outputId');
            $table->string('outputPresetId');
            $table->string('outputKey');
            $table->string('outputStatus');
            $table->integer('outputDuration');
            $table->integer('outputWidth');
            $table->integer('outputHeight');
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
        Schema::dropIfExists('elastic_transcoder_jobs');
    }
}
