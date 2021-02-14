<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddActivityColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('users', function (Blueprint $table) {
		    $table->timestamp('last_activity')->after('billing_status')
			    ->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))
			    ->comment('Last Activity');
		    $table->string('login_city')->after('last_activity')->nullable()->comment('Connected City');
		    $table->string('login_country')->after('login_city')->nullable()->comment('Connected Country');
		    $table->boolean('user_status')->default(1)->after('login_country')->comment('Enable, Disable');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('users', function (Blueprint $table) {
		    $table->dropColumn('last_activity');
		    $table->dropColumn('login_city');
		    $table->dropColumn('login_country');
		    $table->dropColumn('user_status');
	    });
    }
}
