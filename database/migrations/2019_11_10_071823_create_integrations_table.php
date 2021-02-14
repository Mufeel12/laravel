<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('service_codes');
        Schema::dropIfExists('oauth_user');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('mailer_settings');
        Schema::dropIfExists('mailer_lists');
        Schema::dropIfExists('comments_read');
        Schema::dropIfExists('cta_elements');
        Schema::dropIfExists('email_html5_forms');

        Schema::create('integrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('display_name');
            $table->string('service_name');
            $table->string('service_key', 50)->index();
            $table->string('api_key')->index();
            $table->string('hash_key')->nullable();
            $table->string('service_url')->nullable();
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('account_id')->nullable();
            $table->text('metadata')->nullable();
            $table->smallInteger('refresh_flag')->default(0);

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integrations');
    }
}
