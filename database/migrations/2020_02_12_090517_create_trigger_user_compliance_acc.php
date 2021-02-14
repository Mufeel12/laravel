<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerUserComplianceAcc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER user_compliance_create_acc AFTER INSERT ON compliance_records
                FOR EACH ROW
                    BEGIN
                        SELECT user_id INTO @user_id FROM compliance_records WHERE id = NEW.id;
                        SELECT count(*) INTO @compliance_count FROM compliance_records AS s
                        WHERE user_id = @user_id;

                        UPDATE account_summary SET compliance = @compliance_count WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_compliance_update_acc AFTER UPDATE ON compliance_records
                FOR EACH ROW
                    BEGIN
                        SELECT user_id INTO @user_id FROM compliance_records WHERE id = NEW.id;
                        SELECT count(*) INTO @compliance_count FROM compliance_records AS s
                        WHERE user_id = @user_id;

                        UPDATE account_summary SET compliance = @compliance_count WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_compliance_delete_acc AFTER DELETE ON compliance_records
                FOR EACH ROW
                    BEGIN
                        SELECT user_id INTO @user_id FROM compliance_records WHERE id = NEW.id;
                        SELECT count(*) INTO @compliance_count FROM compliance_records AS s
                        WHERE user_id = @user_id;

                        UPDATE account_summary SET compliance = @compliance_count WHERE user_id = @user_id;
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
        DB::unprepared('DROP TRIGGER user_compliance_create_acc');
        DB::unprepared('DROP TRIGGER user_compliance_update_acc');
        DB::unprepared('DROP TRIGGER user_compliance_delete_acc');
    }
}
