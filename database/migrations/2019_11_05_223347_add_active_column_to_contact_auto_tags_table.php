<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveColumnToContactAutoTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_auto_tags', function (Blueprint $table) {
            $table->boolean('active')->default(1);
            $table->boolean('completed')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_auto_tags', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('completed');
        });
    }
}
