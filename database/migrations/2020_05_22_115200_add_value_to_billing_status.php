<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class AddValueToBillingStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users MODIFY billing_status ENUM('Active', 'Inactive', 'Trial', 'Expired', 'Cancelled', 'Failed', 'VerifyRequired','suspended')");
            
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
            DB::statement("ALTER TABLE users MODIFY billing_status ENUM('Active', 'Inactive', 'Trial', 'Expired', 'Cancelled', 'Failed', 'VerifyRequired')");
        });
    }
}
