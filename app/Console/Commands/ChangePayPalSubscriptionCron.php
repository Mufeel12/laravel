<?php


namespace App\Console\Commands;

use App\UserChangeSubscription;
use Braintree_Gateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Spark\Spark;

class ChangePayPalSubscriptionCron extends Command
{
    protected $signature = 'subscription:change';
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
     * @throws \Braintree\Exception\NotFound
     */
    public function handle()
    {
        $gateway = new Braintree_Gateway(config('services.braintree'));
        $changeSubscriptionsQuery = UserChangeSubscription::where('change_date', '=', date('Y-m-d'));
        $userIdChangeSubscriptions = $changeSubscriptionsQuery
            ->pluck('user_id')
            ->toArray();

        $users = \App\User::whereIn('id', $userIdChangeSubscriptions)
            ->where('payment_method', 'paypal')
            ->get();

        foreach($users as $user) {
            $token = $gateway->customer()->find($user->braintree_id)->paymentMethods[0]->token;
            $user->cancelSubscriptionPayPal();
            $changeSubscription = $changeSubscriptionsQuery
                ->where('user_id', $user->id)
                ->first();
            $new_sub = $gateway->subscription()->create([
                'paymentMethodToken' => $token,
                'planId' => $changeSubscription->new_plan,
                'price' => $changeSubscription->payment_adjustment,
                'trialPeriod' => false,
            ]);
            $planInterval = Spark::allPlans()->where('id', $changeSubscription->new_plan)->first()->interval;
            $interval = $planInterval === 'monthly' ? '+1 month' : '+1 year';


            $subscription = $user->currentPlan;
            $subscription->stripe_id = $new_sub->subscription->id;
            $subscription->stripe_plan = $changeSubscription->new_plan;
            $subscription->trial_ends_at = null;
            $subscription->ends_at = date('Y-m-d H:i:s', strtotime($interval));
            $subscription->save();

            $user->billing_status = 'Active';
            $user->trial_ends_at = null;
            $user->save();
        }
    }
}