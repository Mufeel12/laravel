<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsIntoInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('original_id')->nullable();
            $table->integer('subscription_id')->nullable();
            $table->string('customer')->nullable();
            $table->string('system_name')->nullable();
            $table->boolean('paid')->default(false);
            $table->string('status')->nullable();
            $table->string('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('original_id');
            $table->dropColumn('customer');
            $table->dropColumn('system_name');
            $table->dropColumn('paid');
            $table->dropColumn('status');
            $table->dropColumn('description');
            $table->dropColumn('subscription_id');
        });
    }
}
