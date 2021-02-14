<?php

namespace Laravel\Spark\Http\Controllers\Auth;

use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Events\Auth\UserRegistered;
use Laravel\Spark\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Laravel\Spark\Contracts\Interactions\Auth\Register;
use Laravel\Spark\Contracts\Http\Requests\Auth\RegisterRequest;
use App\User;
use App\Stage;
use Carbon\Carbon;
use App\UserSettings;
use App\BlockedEmail;
use App\PayKickPlan;
use App\PayKickSubscription;
use App\UserSubscriptions;
use App\SubscriptionItem;
use App\MeterdPlanInfo;
use Stripe\Stripe;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

use App\Http\Controllers\Api\AuthController;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\CreateTeam;
// use App\SubscriptionItem;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use RedirectsUsers;
    protected $hasher;
    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(HasherContract $hasher)
    {
        $this->middleware('guest');
        $this->hasher = $hasher;
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $this->redirectTo = Spark::afterLoginRedirect();
    }

    /**
     * Show the application registration form.
     *
     * @param  Request  $request
     * @return Response
     */
    public function showRegistrationForm(Request $request)
    {
        if (Spark::promotion() && ! $request->filled('coupon')) {
            // If the application is running a site-wide promotion, we will redirect the user
            // to a register URL that contains the promotional coupon ID, which will force
            // all new registrations to use this coupon when creating the subscriptions.
            return redirect($request->fullUrlWithQuery([
                'coupon' => Spark::promotion()
            ]));
        }

        return view('spark::auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  RegisterRequest  $request
     * @return Response
     */
    public function register(RegisterRequest $request)
    {
        Auth::login($user = Spark::interact(
            Register::class, [$request]
        ));

        event(new UserRegistered($user));

        return response()->json([
            'redirect' => $this->redirectPath()
        ]);
    }

    function payStickRegister(Request $req){
        info('PayStick---'.json_encode($req->all()));
        $data = $req->all();
        // $campaign_id = $data['campaign_id'];
        // $amount = $data['amount'];
        // $email = $data['buyer_email'];
        // $first_name = $data['buyer_first_name'];
        // $last_name = $data['buyer_last_name'];
        // $invoice_id = $data['invoice_id'];
        // $product_id = $data['product_id'];
        $this->registerUser($req);

    } 
    
    function checkforplanupdate($request,$user){
        $paystartplan = PayKickPlan::where('product_id',$request->product_id)->first();
        $plan = Spark::teamPlans()->where('id', $paystartplan->stripe_id)->first();
		$subscriptionCur = $user->currentPlan;
		$update_subscription = $this->updateSubscription($user,$plan);
        PayKickSubscription::where(['user_id'=>$user->id])->update(['plan_id'=>$paystartplan->id]);
		UserSubscriptions::where(['user_id' => $user->id, 'stripe_id' => $subscriptionCur->stripe_id])->update(['stripe_id' => $update_subscription->id,'stripe_plan'=>$plan->id]);
		$interval = $plan->interval === 'monthly' ? '+1 month' : '+1 year';
		$subscriptionCur->ends_at = date('Y-m-d H:i:s', strtotime($interval));
		$subscriptionCur->trial_ends_at = null;
		$subscriptionCur->name = $plan->name;
		$subscriptionCur->stripe_plan = $plan->id;
		$subscriptionCur->stripe_id = $update_subscription->id;
		$subscriptionCur->save();
		$updatedPlan = $user->currentPlan->name;
		$user->billing_status = 'Active';
		$user->trial_ends_at = null;
		$user->save();

    }

    function registerUser($request){
		$checkemail = User::where('email', $request->buyer_email)->first();
		$user = $checkemail; // $user was undefined.
		if ($checkemail) {
            if(($user->currentPlan->stripe_plan=='marketer-paykickstart-static' || $user->currentPlan->stripe_plan=='commercial-paykickstart-static' || $user->currentPlan->stripe_plan=='personal-paykickstart-static') && $request->product_id=='44256'){
            $this->checkforplanupdate($request,$checkemail);
            }
            info('mail-exist');
			return true;
		}
        
		$name = $request->buyer_first_name . ' ' . $request->buyer_last_name;
		$timezone = $request->timezone ?? config('app.timezone');
		$status = \App\Status::where('name', 'active')->first();
		$user = new User();
		$user->name = $name;
		$user->email = $request->buyer_email;
		$signup_source = 'paykickstart';
		 
		$user->referral_source = 'paykickstart';
        $user->signup_source = $signup_source;
        $pass = $this->random_strings(10);
        $pass = 'adilo1234';
        info('Password--'.$pass);
		$user->password = $this->hasher->make($pass);
		$user->trial_ends_at = null;
		$user->billing_status = 'Active';
		$user->status_id = $status->id;
		$user->save();
		$this->setLoginActivity($request, $user);
		$ip = $request->getClientIp();
		UserSettings::createDefaultSettings($user, $timezone, $ip);
		if (!Spark::createsAdditionalTeams()) {
			abort(404);
		}

		$params = [
			'name' => 'MyDefaultTeam' . $user->id,
			'slug' => 'my-default-team-' . $user->id . '-' . time(),
		];

		$team = Spark::interact(CreateTeam::class, [
			$user, $params,
		]);

		$user->switchToTeam($team);

		Stage::createDefaultStage($user);

        $user = User::getUserDetails($user);
        
        $paykickPlan = $this->createPayKickSubscription($user,$request->product_id);
        $this->startFreeForever($user->id,$paykickPlan->stripe_id);
        Mail::send('/subscription/paystartwelcom', ['company' => Spark::$details,
				'full_name' => $user->full_name,
				'plan' => $paykickPlan->name,
				'username' => $user->email,
				'password' => $pass,
				'base_url' => config('app.root_url'),
				'site_name' => config('app.site_url'),
			], function ($m) use ($user) {
				$m->from('accounts@bigcommand.com', 'Bigcommand Accounts');
				$m->to($user->email)->subject('[BigCommand] Welcome to Adilo cloud video hosting');
			});    
    }

    private function setLoginActivity(Request $request, $user) {
		$ip = $request->getClientIp();
		$geo_location = geoip()->getLocation($ip);

		$user->last_activity = now($user->settings->timezone);
		$user->login_country = $geo_location['iso_code'];
		$user->login_city = $geo_location['city'];
		$user->save();
    }
    
    function random_strings($length_of_string) 
    { 
    
        // String of all alphanumeric character 
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
    
        // Shufle the $str_result and returns substring 
        // of specified length 
        return substr(str_shuffle($str_result),  
                        0, $length_of_string); 
    }

    public function startFreeForever($user_id,$itemid) {
        $user = User::where('id', $user_id)->firstOrFail();
        $status = \App\Status::where('name', 'active')->first();
        // info(json_encode($user->currentPlan));die;
        $user->billing_status = 'Active';
        $user->email_verified_token = Str::random(64);
        $user->status_id = $status->id;
        $customer = $this->createStripeCustomer($user);
        $user->stripe_id = $customer->id;
        $user->payment_method = 'stripe';
        $user->save();
        info('startFreeForever-');
        if (empty($user->currentPlan)) {

            $this->startFreeSubscription($user,$itemid);
        }

     return $user;
    }

    function createPayKickSubscription($user,$productId){
        info('startFreeForever-'.$productId);
        $plan = PayKickPlan::where('product_id',$productId)->first();

        $data = [];
        $data['plan_id'] = $plan->id;
        $data['user_id'] = $user->id;
        $data['start_date'] = Carbon::now();
        $subid = PayKickSubscription::insertGetId($data);
        User::where('id',$user->id)->update(['paykick_subscription_id'=>$subid]); 
        info('subscription done-');
        return $plan;

    }

    /**
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	function createStripeCustomer($user) {
		$customer = \Stripe\Customer::create([
			'email' => $user->email,
		]);
		// $source =  $customer->createSource(
		// 	$customer->id,
		// 	['source' => $token]
		//   );
		//   $customer->update($customer->id,['default_source'=>$source->id]);
		return $customer;

    }
    
    function startFreeSubscription($user,$itemid) {

		$plan = Spark::teamPlans()->where('id', $itemid)->first();
		$mertedplan = MeterdPlanInfo::where(['plan_type' => 'free'])->get();
		$items = [];
		array_push($items, ['price' => $itemid]);
		$interval = $plan->interval === 'monthly' ? '+1 month' : '+1 year';

		// if (count($mertedplan) > 0) {
		// 	foreach ($mertedplan as $merted) {
		// 		$items[] = ['price' => $merted->plan_id];
		// 	}
		// }
		$new_subscription = \Stripe\Subscription::create([
			'customer' => $user->stripe_id,
			'items' => [['price'=>$itemid]],
			"trial_end" => 'now',
			//"default_payment_method"=>$tokenData['id']
		]);
		$subscription = $user->subscriptions()->create([
			'name' => $plan->name,
			'stripe_id' => $new_subscription->id,
			'stripe_plan' => $plan->id,
			'quantity' => 1,
			'trial_ends_at' => $user->trial_ends_at,
			'ends_at' => date('Y-m-d H:i:s', strtotime($interval)),
		]);
		$subscriptionitems = $new_subscription->items;
		// info(json_encode($subscriptionitems));
		info(json_encode($subscription->id));
		foreach ($subscriptionitems->data as $row) {
			\App\SubscriptionItem::insert([
				'subscription_id' => $subscription->id,
				'subscription_stripe_id' => $new_subscription->id,
				'subscription_type' => $plan->interval,
				'user_id' => $user->id,
				'stripe_id' => $row->id,
				'stripe_plan' => $row->plan->id,
				"created_at" => \Carbon\Carbon::now(), # new \Datetime()
				"updated_at" => \Carbon\Carbon::now(), # new \Datetime()
			]);
		}
		UserSubscriptions::insert(['user_id' => $user->id, 'stripe_id' => $new_subscription->id,'stripe_plan'=>$row->plan->id]);
    }
    

    function updateSubscription($user, $new_plan) {
		$subscriptionCur = $user->currentPlan;
		$trailEnd = 'now';
		$res = SubscriptionItem::where('subscription_id', $user->currentPlan->id)->get();
		$subscriptionItem = [];
		foreach ($res as $val) {
			$subscriptionItem[$val->stripe_plan] = $val->stripe_id;
		}
		try {
            $subItems = [[
                'id' => $subscriptionItem[$user->currentPlan->stripe_plan],
                'price' => $new_plan->id,
            ]];
            $trailEnd = 'now';
            $stripe_request['items'] = $subItems;
		    $stripe_request['trial_end'] = $trailEnd;
		    $stripe_request['cancel_at_period_end'] = false;
			$update_subscription = \Stripe\Subscription::update($subscriptionCur->stripe_id, $stripe_request);
			$this->updateSubscriptionItems($new_plan, $user, $update_subscription);
			UserSubscriptions::where(['user_id' => $user->id, 'stripe_id' => $subscriptionCur->stripe_id])->update(['stripe_id' => $update_subscription->id,'stripe_plan'=>$new_plan->id]);
			UserSubscriptions::where(['user_id' => $user->id])->where('stripe_id', '!=', $update_subscription->id)
				->delete();
			return $update_subscription;
		} catch (Exception $e) {
			return $e->getMessage();
		}

    }
    
    	/*
		*update subscription item
	*/
	function updateSubscriptionItems($plan, $user, $update_subscription) {

		$subscriptionitems = $update_subscription->items;
		$subscription_id = $user->currentPlan->id;
		SubscriptionItem::where(['subscription_id' => $subscription_id])->delete();
		info('-=====');
		info(json_encode($subscriptionitems->data));
		info(json_encode($plan));
		info('-=====');

		foreach ($subscriptionitems->data as $row) {
			\App\SubscriptionItem::insert([
				'subscription_id' => $subscription_id,
				'subscription_stripe_id' => $update_subscription->id,
				'subscription_type' => $plan->interval,
				'stripe_id' => $row->id,
				'user_id' => $user->id,
				'stripe_plan' => $row->plan->id,
				"created_at" => \Carbon\Carbon::now(), # new \Datetime()
				"updated_at" => \Carbon\Carbon::now(), # new \Datetime()
			]);
		}
	}
}
