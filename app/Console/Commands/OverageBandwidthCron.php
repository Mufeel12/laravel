<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OverageBandwidthCron extends Command
{
    protected $signature = 'overage-bandwidth:update';
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
        $users = \App\User::all();

        foreach ($users as $user) {
            $plan = $user->currentPlan()->first();
            if($plan) {
                $overage = $user->getBandwidthOverage();
                if($overage->size > 0) {
                    $user->includeOverage($overage->cost);
                }
            }
        }
    }
}