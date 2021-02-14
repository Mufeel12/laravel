<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnContactInfoInSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('settings', function (Blueprint $table) {
		    $table->string('street_address')->after('user_image')->nullable()->comment('Settings Street Address');
		    $table->string('apartment_suite')->after('street_address')->nullable()->comment('Settings Apartment & Suite');
		    $table->string('city')->after('apartment_suite')->nullable()->comment('Settings City');
		    $table->string('state', 50)->after('city')->nullable()->comment('Settings State/Province/Region');
		    $table->string('country', 50)->after('state')->nullable()->comment('Settings Country');
		    $table->string('zip_code', 20)->after('country')->nullable()->comment('Settings Zip Code');
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
		    $table->dropColumn('street_address');
		    $table->dropColumn('apartment_suite');
		    $table->dropColumn('city');
		    $table->dropColumn('state');
		    $table->dropColumn('zip_code');
	    });
    }
}
