<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComplianceRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compliance_records', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('issue_id');
            $table->enum('resolution', ['suspended', 'deleted']);
            $table->timestamp('created_at');
            $table->timestamp('ends_at')->nullable('true');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('compliance_records');
    }
}
