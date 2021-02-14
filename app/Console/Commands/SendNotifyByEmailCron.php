<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Laravel\Spark\Spark;
use Carbon\Carbon;

class SendNotifyByEmailCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-notify:start';

    /**
     * The console command description.
     *
     * @var string
     */
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
        $subjects = config('app.mail_subjects');

        $users = \App\User::with('settings')
            ->leftJoin('settings', 'user_id', 'users.id')
            ->where('settings.bill_estimates', 1)
            ->get();

        $date = new Carbon();

        foreach ($users as $user) {
            $subscriptionEnd = $user->currentPlan->ends_at;
            $dayCheck = date('d', strtotime('-14 days', strtotime($subscriptionEnd)));
            $email = $user->settings->notify_email ? $user->settings->notify_email : $user->email;

            if(date("Y-m-d") === date("Y-m-$dayCheck")) {
                $plan = Spark::teamPlans()->where('id', $this->user->stripe_plan)->first();
                Mail::send('/billing/estimate', [
                    'user' => $user,
                    'company' => Spark::$details,
                    'plan' => $plan,
                    'date' => $date->toFormattedDateString(),
                    'estimate' => $user->getBillingEstimate(),
                    'base_url' => config('app.root_url'),
                    'site_name' => config('app.site_url'),
                    'title' => 'Here\'s your monthly estimate.'
                ], function ($m) use ($email, $subjects) {
                    $m->to($email)->subject($subjects['estimate']);
                });
            }
        }
    }
}