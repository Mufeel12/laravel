<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSnapPageMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('snap_page_menus', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('snap_page_id')->unsigned();
            $table->text('menus');
            $table->timestamps();

            $table->foreign('snap_page_id')->references('id')->on('snap_pages')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('snap_page_menus');
    }
}
