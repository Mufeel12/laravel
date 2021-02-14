<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerUserProjectAcc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER user_project_create_acc AFTER INSERT ON projects
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM projects WHERE id = NEW.id;
                        SELECT count(*) INTO @projects_count FROM projects AS p
                        WHERE owner = @user_id;

                        UPDATE account_summary SET projects_count = @projects_count WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_project_update_acc AFTER UPDATE ON projects
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM projects WHERE id = NEW.id;
                        SELECT count(*) INTO @projects_count FROM projects AS p
                        WHERE owner = @user_id;

                        UPDATE account_summary SET projects_count = @projects_count WHERE user_id = @user_id;
                    END;
                    
        CREATE TRIGGER user_project_delete_acc AFTER DELETE ON projects
                FOR EACH ROW
                    BEGIN
                        SELECT owner INTO @user_id FROM projects WHERE id = NEW.id;
                        SELECT count(*) INTO @projects_count FROM projects AS p
                        WHERE owner = @user_id;

                        UPDATE account_summary SET projects_count = @projects_count WHERE user_id = @user_id;
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
        DB::unprepared('DROP TRIGGER user_project_create_acc');
        DB::unprepared('DROP TRIGGER user_project_update_acc');
        DB::unprepared('DROP TRIGGER user_project_delete_acc');
    }
}
