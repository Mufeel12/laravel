<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetDefaultValuesForPrivacy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $results = DB::table('video_player_options')->get();

        $i = 1;
        foreach ($results as $result){
            DB::table('video_player_options')
                ->where('id',$result->id)
                ->update([
                    "commenting_permissions" => "everyone",
                    "embed_settings" => "any",
                    "permissions" => "everyone",
                ]);
            $i++;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
