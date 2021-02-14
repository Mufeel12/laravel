<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTagsColumnToSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('subscribers', function (Blueprint $table) {
		    $table->text('tags')->after('twitter_name')->nullable()->comment('Tags');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('subscribers', function (Blueprint $table) {
		    $table->dropColumn('tags');
	    });
    }
}
