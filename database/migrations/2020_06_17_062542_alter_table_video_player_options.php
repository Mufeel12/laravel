<?php

use App\VideoPlayerOption;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableVideoPlayerOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        VideoPlayerOption::where('embed_settings', 'inherit')->update(['embed_settings' => 'any']);
        VideoPlayerOption::where('commenting_permissions', 'inherit')->update(['commenting_permissions' => 'any']);
        VideoPlayerOption::where('permissions', 'inherit')->update(['permissions' => 'any']);
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
