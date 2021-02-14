<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserStatusUpdate extends Command
{
    protected $signature = 'user-status:update';
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = date('Y-m-d H:i:s');
        $usersIdForUnlock = DB::table('compliance_records')
            ->where('ends_at', '<', $today)
            ->pluck('user_id')
            ->toArray();

        $status = DB::table('statuses')->where('name', 'active')->first();

        foreach ($usersIdForUnlock as $userId) {
            $user = \App\User::where('id', $userId)->first();

            if(isset($user)) {
                $user->status_id = $status->id;
                $user->save();
            }
        }
    }
}