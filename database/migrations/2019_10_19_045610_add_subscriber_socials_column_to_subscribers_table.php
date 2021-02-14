<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriberSocialsColumnToSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('subscribers', function (Blueprint $table) {
		    $table->text('photo_url')->nullable()->comment('Photo Url');
		    $table->string('facebook_name')->after('facebook_link')->nullable()->comment('Facebook Profile Name');
		    $table->string('linked_in_name')->after('linked_in_link')->nullable()->comment('LinkedIn Profile Name');
		    $table->string('twitter_name')->after('twitter_link')->nullable()->comment('Twitter Profile Name');
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
		    $table->dropColumn('photo_url');
		    $table->dropColumn('facebook_name');
		    $table->dropColumn('linked_in_name');
		    $table->dropColumn('twitter_name');
	    });
    }
}
