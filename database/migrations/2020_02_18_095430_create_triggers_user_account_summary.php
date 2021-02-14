<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggersUserAccountSummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE TRIGGER user_account_summary_create AFTER INSERT ON users
                FOR EACH ROW
                BEGIN
                    INSERT INTO account_summary (user_id) VALUES (NEW.id);
                END;
        ');
        DB::unprepared('
            CREATE TRIGGER user_account_summary_delete AFTER DELETE ON users
                FOR EACH ROW
                BEGIN
                    DELETE FROM account_summary WHERE user_id = OLD.id;
                END;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER user_account_summary_create');
        DB::unprepared('DROP TRIGGER user_account_summary_delete');
    }
}
