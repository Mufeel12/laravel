<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSnapPageLinkToSnapPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('snap_pages', function (Blueprint $table) {
            $table->string('snap_page_link')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('snap_pages', function (Blueprint $table) {
            $table->dropColumn('snap_page_link');
        });
    }
}
