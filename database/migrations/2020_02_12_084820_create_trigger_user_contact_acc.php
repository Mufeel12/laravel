<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerUserContactAcc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER user_contact_create_acc AFTER INSERT ON subscribers
                FOR EACH ROW
                    BEGIN
                        SELECT user_id INTO @user_id FROM subscribers WHERE id = NEW.id;
                        SELECT count(*) INTO @contact_count FROM subscribers AS s
                        WHERE user_id = @user_id;

                        UPDATE account_summary SET contact_size = @contact_count WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_contact_update_acc AFTER UPDATE ON subscribers
                FOR EACH ROW
                    BEGIN
                        SELECT user_id INTO @user_id FROM subscribers WHERE id = NEW.id;
                        SELECT count(*) INTO @contact_count FROM subscribers AS s
                        WHERE user_id = @user_id;

                        UPDATE account_summary SET contact_size = @contact_count WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_contact_delete_acc AFTER DELETE ON subscribers
                FOR EACH ROW
                    BEGIN
                        SELECT user_id INTO @user_id FROM subscribers WHERE id = NEW.id;
                        SELECT count(*) INTO @contact_count FROM subscribers AS s
                        WHERE user_id = @user_id;

                        UPDATE account_summary SET contact_size = @contact_count WHERE user_id = @user_id;
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
        DB::unprepared('DROP TRIGGER user_contact_create_acc');
        DB::unprepared('DROP TRIGGER user_contact_update_acc');
        DB::unprepared('DROP TRIGGER user_contact_delete_acc');
    }
}
