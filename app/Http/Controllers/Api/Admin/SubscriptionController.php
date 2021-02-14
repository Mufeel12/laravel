<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\RecalcSubscriptionRequest;
use App\Http\Requests\AdminChangePlanRequest;
use App\Http\Controllers\Controller;
use App\User;
use Laravel\Spark\Spark;
use Carbon\Carbon;
use App\UserChangeSubscription;

class SubscriptionController extends Controller
{
    protected $cycle_options = [];
    protected $user_payment_options = [];

    protected function getPaymentAdjustment($data, $user)
    {
        $plan = Spark::teamPlans()->where('id', $data['newPlan']);

        switch ($data['cycleOption']) {
            case 'end_cycle':
                return [ 'price' => 0 ];

            case 'on_date':
                return [ 'price' => $user->recalcPlanChange($data['newPlan'], $data['date']) ];

            case 'today':
            default:
                return [ 'price' => $user->recalcPlanChange($data['newPlan'], Carbon::now()) ];
        }
    }

    public function __construct()
    {
        $this->cycle_options = config('app.cycle_options');
        $this->user_payment_options = config('app.payment_options');
    }

    public function index(Request $request, $user_id)
    {
        $user = User::with('currentPlan')->findOrFail($user_id);
        $subscription = $user->currentPlan;

        return [
            'user'               => $user,
            'currentPlan'        => Spark::teamPlans()->where('id', $subscription->stripe_plan)->first(),
            'plans'              => Spark::teamPlans()->all(),
            'cycleOptions'       => array_values($this->cycle_options),
            'userPaymentOptions' => array_values($this->user_payment_options),
        ];
    }

    public function recalc(RecalcSubscriptionRequest $request, $user_id)
    {
        $data = $request->validated();
        $user = User::findOrFail($user_id);

        if(!isset($data['date']) && $data['cycleOption'] === 'on_date') {
            return \App::abort(400, 'date field is required');
        }

        return $this->getPaymentAdjustment($data, $user);
    }

    public function changePlan(AdminChangePlanRequest $request, $user_id)
    {
        $data = $request->validated();
        $user = User::findOrFail($user_id);
        $plan = Spark::teamPlans()->where('id', $data['newPlan']);

        if(!isset($data['date']) && $data['cycleOption'] === 'on_date') {
            return \App::abort(400, 'date field is required');
        }

        $payment_adjustment = $this->getPaymentAdjustment($data, $user);

        if ($data['cycleOption'] === 'today' || $data['cycleOption'] === 'on_date') {
            $date = $data['cycleOption'] === 'today' ? Carbon::now() : new Carbon($data['date']);
            $user->changePlan($data, $payment_adjustment['price']);
            UserChangeSubscription::create([
                'user_id'            => $user_id,
                'admin_id'           => $request->user()->id,
                'old_plan'           => $user->currentPlan->stripe_plan,
                'new_plan'           => $data['newPlan'],
                'cycle_option'       => $data['cycle_option'],
                'payment_option'     => $data['payment_option'],
                'payment_adjustment' => $payment_adjustment['price'],
                'change_date'        => $date
            ]);
            return $user->currentPlan;
        }

        $user->changePlan($data, 0);

        return $user->currentPlan;
    }
}