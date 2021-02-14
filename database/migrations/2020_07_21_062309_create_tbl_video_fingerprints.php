<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblVideoFingerprints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_fingerprints', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('video_id');
            $table->text('fkey');
            $table->text('userAgent')->nullable();
            $table->text('webdriver')->nullable();
            $table->text('language')->nullable();
            $table->unsignedInteger('colorDepth')->nullable();
            $table->text('deviceMemory')->nullable();
            $table->unsignedInteger('hardwareConcurrency')->nullable();
            $table->text('screenResolution')->nullable();
            $table->text('availableScreenResolution')->nullable();
            $table->integer('timezoneOffset')->nullable();
            $table->text('timezone')->nullable();
            $table->text('sessionStorage')->nullable();
            $table->text('localStorage')->nullable();
            $table->text('indexedDb')->nullable();
            $table->text('addBehavior')->nullable();
            $table->text('openDatabase')->nullable();
            $table->text('platform')->nullable();
            $table->text('plugins')->nullable();
            $table->text('canvas')->nullable();
            $table->text('webgl')->nullable();
            $table->text('webglVendorAndRenderer')->nullable();
            $table->text('adBlock')->nullable();
            $table->text('hasLiedLanguages')->nullable();
            $table->text('hasLiedResolution')->nullable();
            $table->text('hasLiedOs')->nullable();
            $table->text('hasLiedBrowser')->nullable();
            $table->text('touchSupport')->nullable();
            $table->text('fonts')->nullable();
            $table->text('audio')->nullable();
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
        //
    }
}
