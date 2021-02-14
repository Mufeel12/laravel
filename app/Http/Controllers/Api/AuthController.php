<?php

namespace App\Http\Controllers\Api;

use App\BlockedEmail;
use App\Card;
use App\FailedPayment;
use App\Http\Controllers\Controller;
use App\Http\Helpers\StripeHelper;
use App\MeterdPlanInfo;
use App\Notifications\ResetPasswordLinkSent;
use App\Notifications\SocialRegisterMail;
use App\Notifications\VerifyEmail;
use App\SignupCoupon;
use App\Stage;
use App\Subscription;
use App\SubscriptionItem;
use App\User;
use App\UserSettings;
use App\UserSubscriptions;
use App\SubscriptionSchedule;
use Braintree_Gateway;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\CreateTeam;
use Laravel\Spark\LocalInvoice as Invoice;
use Laravel\Spark\Spark;
use Lcobucci\JWT\Parser;
use Psr\Http\Message\StreamInterface;
use Stripe\Stripe;

class AuthController extends Controller {
	protected $hasher;
	protected $mailchimp;
	public function __construct(HasherContract $hasher) {
		
		$this->hasher = $hasher;
		\Stripe\Stripe::setApiKey(config('services.stripe.secret'));

	}

	/**
	 * Check account exists.
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function checkEmailExist(Request $request) {
		$email = $request->input('email');
		$user = User::where('email', $email)->first();
		$blocked = false;

		if ($user && !is_null($user)) {
			if ($user->billing_status == 'Cancelled') {
				return response()->json([
					'result' => 'cancelled',
					'user' => $user,
				]);
			} elseif ($user->status_id === 3) {
				return response()->json([
					'result' => 'error',
					'message' => "We couldn't find your " . config('app.name') . ' Account',
				]);
			} elseif ($user->billing_status == 'VerifyRequired') {
				return response()->json([
					'result' => 'verify-required',
					'message' => "This account is not verified, please check your email.",
					'userId' => $user->id,
				]);
			} else {
				$blocked = BlockedEmail::where('email', $user->email)->first();
				if ($blocked) {
					return response()->json([
						'result' => 'blocked',
						'message' => "This email is not allowed to sign up.",
					]);
				}
				return response()->json([
					'result' => 'success',
				]);
			}
		} else {
			return response()->json([
				'result' => 'error',
				'message' => "We couldn't find your " . config('app.name') . ' Account',
			]);
		}
	}

	/**
	 * User Login Process
	 *
	 * @param Request $request
	 * @return JsonResponse|StreamInterface
	 */
	public function login(Request $request) {

		$credentials = [
			'email' => $request->input('email'),
			'password' => $request->input('password'),
		];

		$remember_me = false;
		if ($request->has('remember_me')) {
			if ($request->input('remember_me') || $request->input('remember_me') == 'true') {
				$remember_me = true;
			}
		}

		$user = User::query()->where('email', $request->input('email'))->first();

		if (!$user || $user->status_id === 3) {
			return response()->json('We couldn’t find your ' . config('app.name') . ' Account.', 401);
		}
		if ($user->admin_user_status === 'pending') {
			return response()->json(['result' => false, 'error' => 'config_pending', 'token' => $user->user_token]);
		}

		if (auth()->attempt($credentials, $remember_me)) {
			$tokenObject = $this->createTokenForUser(auth()->user());

			auth()->user()->last_activity = date('Y-m-d H:i:s');
			auth()->user()->save();

			if ($remember_me) {
				$tokenObject->token->expires_at = now()->addDays(config('services.passport.expires_remember_me'));
				$tokenObject->token->update([
					'expires_at' => now()->addDays(config('services.passport.expires_remember_me')),
				]);
			}

			$user = auth()->user();
			$this->setLoginActivity($request, $user);

			Stage::createDefaultStage($user);
			addToLog(['user_id' => $user->id, 'activity_type' => 'login', 'subject' => 'Logged in to account
			', ]);
			return response()->json([
				'access_token' => $tokenObject->accessToken,
				'expires_in' => $tokenObject->token->expires_at->diffInSeconds(now()),
			], 200);
		} else {
			return response()->json('Wrong password. Try again or click forgot password to reset it.', 401);
		}
	}

	private function createTokenForUser($user) {
		//$timezone = $user->settings->timezone ? $user->settings->timezone : config('app.timezone');
		//
		//		config(['app.timezone' => $timezone]);

		$tokenObject = $user->createToken('BCForWeb');

		$tokenObject->token->expires_at = now()->addHours(config('services.passport.expires_hours'));
		$tokenObject->token->update([
			'expires_at' => now()->addHours(config('services.passport.expires_hours')),
		]);

		return $tokenObject;
	}

	private function setLoginActivity(Request $request, $user) {
		$ip = $request->getClientIp();
		$geo_location = geoip()->getLocation($ip);

		$user->last_activity = now($user->settings->timezone);
		$user->login_country = $geo_location['iso_code'];
		$user->login_city = $geo_location['city'];
		$user->save();
	}

	/**
	 * Authentication Logout
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function logout(Request $request) {
		$value = $request->bearerToken();
		$id = (new Parser())->parse($value)->getHeader('jti');
		$token = $request->user()->tokens->find($id);
		$user = $request->user();
		if ($token) {
			$token->delete();
		}
		addToLog(['user_id' => $user->id,
			'activity_type' => 'logout',
			'subject' => 'Logged out of account',
		]);
		return response()->json([
			'result' => 'success',
		], 200);
	}

	/**
	 * @param Request $request
	 * @return mixed
	 * @throws
	 */
	public function register(Request $request) {

		$this->validate($request, [
			'first_name' => 'required|string|max:191',
			'email' => 'required|string|email|max:191|unique:users',
			'password' => 'required|string|min:8',
		]);
		$blocked = BlockedEmail::where('email', $request->email)->first();
		if ($blocked) {
			return response()->json([
				'success' => false,
				'result' => 'blocked',
			]);
		}

		$name = $request->first_name . ' ' . $request->last_name;
		$timezone = $request->timezone ?? config('app.timezone');

		$status = \App\Status::where('name', 'brand_new_not_activated')->first();
		$user = new User();
		$user->name = $name;
		$user->email = $request->email;
		$signup_source = '';
		if ($request->has('signup_source')) {
			$signup_source = implode(',', json_decode($request->signup_source));
		}
		$user->referral_source = $request->referral_source;
		$user->signup_source = $signup_source;
		$user->password = $this->hasher->make($request->password);
		$user->trial_ends_at = now()->addDays(config('services.subscription.trial_duration'));
		$user->billing_status = 'Inactive';
		$user->status_id = ;
		// $user->status_id = $status->id;
		$user->save();
		$this->setLoginActivity($request, $user);
		$ip = $request->getClientIp();

		UserSettings::createDefaultSettings($user, $timezone, $ip);

		$tokenObject = $this->createTokenForUser($user);

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

		return response()->json([
			'access_token' => $tokenObject->accessToken,
			'expires_in' => $tokenObject->token->expires_at->diffInSeconds(now()),
			'email' => $user->email,
			'name' => $user->name,
			'id' => $user->id,
			'userInfo' => $user,
			'result' => 'success',
		], 200);
	}

	/**
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	function createStripeCustomerWithSource($user, $token) {

		try {
			$customer = \Stripe\Customer::create([
				'email' => $user->email,
			]);
			$res2 = \Stripe\Token::retrieve($token);

			$cardResponse = $res2->card;
			info('cardResponse' . json_encode($cardResponse));
			$source = $customer->createSource(
				$customer->id,
				['source' => $token]
			);
			$card_ = $res2->card;
			info('createSourceResponse' . json_encode($card_));
			$card = Card::where(['user_id' => $user->id, 'fingerprint' => $res2->card->fingerprint])->first();
			if ($card == null) {
				$card = Card::create([
					'user_id' => $user->id,
					'fingerprint' => $res2->card->fingerprint,
					'source_id' => $source->id,
					'exp_year' => $card_->exp_year,
					'exp_month' => $card_->exp_month,
					'card_brand' => $card_->brand,
					'card_last_four' => $card_->last4,
					'default_card' => 'yes',
				]);
			} else {
				Card::where(['user_id' => $user->id])->update([
					'fingerprint' => $res2->card->fingerprint,
					'source_id' => $source->id,
				]);
			}

			$customer->update($customer->id, ['default_source' => $source->id]);
			//info(json_encode($customer));

			return ['result' => 'success', 'customer' => $customer];

		} catch (\Stripe\Exception\CardException $e) {
			return ['result' => 'error', 'error' => $e->getError()->message];
		} catch (\Stripe\Exception\Card $e) {

			return ['result' => 'error', 'error' => $e->getError()->message];
		} catch (\Stripe\Exception\RateLimitException $e) {
			return ['result' => 'error', 'error' => $e->getError()->message];
		} catch (\Stripe\Exception\InvalidRequestException $e) {
			return ['result' => 'error', 'error' => $e->getError()->message];
		} catch (\Stripe\Exception\AuthenticationException $e) {
			return ['result' => 'error', 'error' => $e->getError()->message];

		} catch (\Stripe\Exception\ApiConnectionException $e) {
			return ['result' => 'error', 'error' => $e->getError()->message];
		} catch (\Stripe\Exception\ApiErrorException $e) {
			return ['result' => 'error', 'error' => $e->getError()->message];

		} catch (Exception $e) {
			return ['result' => 'error', 'error' => $e->getMessage()];
		}

		return ['result' => 'error', 'error' => 'Invalid card'];

	}
	/**
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function startFreeTrial(Request $request) {
 
		$user = $request->user();
 
		try{
			$user->billing_status = 'Trial';
			$subscription = $request->input('subscription');
			$tokenData = $request->input('tokenData');
			$cardData = $tokenData['card'];
			$getCustomer = $this->createStripeCustomerWithSource($user, $tokenData['id']);
			info(json_encode($getCustomer));
			if ($getCustomer['result'] != 'success') {
				if (strpos($subscription['plan_id'], 'bundle') !== false) {
					User::where('id',$user->id)->delete();
				}
				return $getCustomer;
			} else {
				$customer = $getCustomer['customer'];
			}

			info('ffff--');
			info($user->is_discount_applied);
			$user->stripe_id = $customer->id;
			//$user->trial_ends_at = now()->addDays(config('services.subscription.trial_duration'));
			if ($subscription['discount']) {
				// User::where('id',$user->id)->update(['is_discount_applied'=>1]);
				$user->trial_ends_at = null;
				$user->is_discount_applied = '1';
				$user->billing_status = 'Active';
				$status = \App\Status::where('name', 'active')->first();
			} else if($subscription['bundle']){
				$user->trial_ends_at = null;
				$user->billing_status = 'Active';
				$status = \App\Status::where('name', 'active')->first();
			}else {
				$status = \App\Status::where('name', 'active')->first();
				$user->trial_ends_at = Carbon::now()->addDays(config('services.subscription.trial_duration'));
			}
			$user->payment_method = $subscription['payment_method'];
			$user->card_brand = isset($cardData['brand']) ? $cardData['brand'] : null;
			$user->card_last_four = isset($cardData['last4']) ? $cardData['last4'] : null;
			$user->exp_month = isset($subscription['exp_month']) ? $subscription['exp_month'] : null;
			$user->exp_year = isset($subscription['exp_year']) ? $subscription['exp_year'] : null;
			$user->status_id = $status->id;
			
			$user->save();
			$user1 = User::where('id', $user->id)->first();

			$plan = Spark::teamPlans()->where('id', $subscription['plan_id'])->first();
			$coupon = SignupCoupon::where(['plan_id' => $subscription['plan'], 'status' => 'active'])->first();
			$subitems = $this->getNewSubscriptionItems($subscription,$plan);
			info('subitems'.json_encode($subitems));
			$items = $subitems['items'];
			$bundleItem = $subitems['bundleItem'];
			$mertedplan = $subitems['mertedplan'];
			$interval_type = $plan->interval;
			$interval = $plan->interval === 'monthly' ? '+1 month' : '+1 year';
			if($subscription['plan_id']=='pro-quarterly-bundle-static'){
				$interval = '+3 month';
				$interval_type = 'quarterly';
			}
			if($subscription['plan_id']=='pro-semi-bundle-static'){
				$interval = '+6 month';
				$interval_type = 'semi';

			}if($subscription['plan_id']=='pro-annual-bundle-static'){
				$interval = '+1 year';
			}if($subscription['plan_id']=='business-quarterly-bundle-static'){
				$interval = '+3 month';
				$interval_type = 'quarterly';

			}if($subscription['plan_id']=='business-semi-bundle-static'){
				$interval = '+6 month';
				$interval_type = 'semi';

			}if($subscription['plan_id']=='business-annual-bundle-static'){
				$interval = '+1 year';
			}
			$requ = [
				'customer' => $user->stripe_id,
				'items' => $items
			];
			info($subscription['discount']);
			if ($coupon != null && $subscription['discount']) {
				$requ['coupon'] = $coupon->coupon_id;
				$requ['trial_end'] = 'now';
			} elseif($subscription['bundle']){
				$requ['trial_end'] = 'now';
			}else {
				$requ['trial_end'] = Carbon::now()->addDays(config('services.subscription.trial_duration'))->timestamp;
				// $requ['trial_end'] = Carbon::now()->addSeconds(60)->timestamp;
			}
			info('jkjdddk---' . json_encode($requ));
			$new_subscription = \Stripe\Subscription::create($requ);
			info('new_subscription---' . json_encode($new_subscription));
			if(strpos($plan->name, 'bundle') !== false){
				$plan->name = str_replace('', '-bundle', $plan->name);
			}
			$subscriptionRequest = [
				'name' => $plan->name,
				'stripe_id' => $new_subscription->id,
				'stripe_plan' => $plan->id,
				'quantity' => 1,
				'trial_ends_at' => $user->trial_ends_at,
			];
			if ($coupon != null && $subscription['discount']) {
				$subscriptionRequest['ends_at'] = date('Y-m-d H:i:s', strtotime($interval));
			} elseif($subscription['bundle']){
				$subscriptionRequest['ends_at'] = date('Y-m-d H:i:s', strtotime($interval));
			} else {
				$subscriptionRequest['ends_at'] = date('Y-m-d H:i:s', strtotime($interval . ' ' . config('services.subscription.trial_duration') . 'days'));
			}
			info('interval---'.json_encode($interval));
			info('subscriptionRequest---'.json_encode($subscriptionRequest));
			$user->subscriptions()->create($subscriptionRequest);
			$subscriptionitems = $new_subscription->items;
			//
			foreach ($subscriptionitems->data as $row) {
				\App\SubscriptionItem::insert([
					'subscription_id' => $user->currentPlan->id,
					'subscription_stripe_id' => $new_subscription->id,
					'subscription_type' => $interval_type,
					'user_id' => $user->id,
					'stripe_id' => $row->id,
					'stripe_plan' => $row->plan->id,
					"created_at" => \Carbon\Carbon::now(), # new \Datetime()
					"updated_at" => \Carbon\Carbon::now(), # new \Datetime()
				]);
			}
			UserSubscriptions::insert(['user_id' => $user->id, 'stripe_id' => $new_subscription->id,'stripe_plan'=>$plan->id]);
			/** Bundle */
			if($subscription['bundle'] && !empty($bundleItem)){
				$bundleRequ = [
					'customer' => $user->stripe_id,
					'items' => $bundleItem,
					'trial_end' => 'now'
				];

				$bundle_subscription = \Stripe\Subscription::create($bundleRequ);
				$bundle_subscriptionitems = $bundle_subscription->items;
				foreach ($bundle_subscriptionitems->data as $row) {
					\App\SubscriptionItem::insert([
						'subscription_id' => $user->currentPlan->id,
						'subscription_stripe_id' => $bundle_subscription->id,
						'subscription_type' => $interval_type,
						'user_id' => $user->id,
						'stripe_id' => $row->id,
						'stripe_plan' => $row->plan->id,
						"created_at" => \Carbon\Carbon::now(), # new \Datetime()
						"updated_at" => \Carbon\Carbon::now(), # new \Datetime()
					]);
				}
				UserSubscriptions::insert(['user_id' => $user->id, 'stripe_id' => $bundle_subscription->id,'stripe_plan'=>$plan->id]);
			}
			/** Bundle */
			$user = User::getUserDetails($request->user());
			Mail::send('/subscription/welcome', ['company' => Spark::$details,
				'full_name' => $user->full_name,
				'plan' => $plan->name,
				'base_url' => config('app.root_url'),
				'site_name' => config('app.site_url'),
			], function ($m) use ($user) {
				$m->from('accounts@bigcommand.com', 'Bigcommand Accounts');
				$m->to($user->email)->subject('[BigCommand] Welcome to Adilo cloud video hosting');
			});
			return response()->json([
				'result' => 'success',
				'userInfo' => $user,
			], 200);
		}catch (Exception $e) {
			if (!isset($subscription['plan_id']) || strpos($subscription['plan_id'], 'bundle') !== false) {
				User::where('id',$user->id)->delete();
		   }
			return $e->getMessage();
		}
	}

	function getNewSubscriptionItems($subscription,$plan){
		info($subscription['plan_id']);
		if($subscription['bundle']){
			if($subscription['plan_id'] =='pro-quarterly-bundle-static' || $subscription['plan_id'] =='pro-semi-bundle-static'){
				$mertedplan = MeterdPlanInfo::where(['plan_type' => 'pro-monthly-static'])->get();
			}else if($subscription['plan_id'] =='pro-annual-bundle-static' ){
				$mertedplan = MeterdPlanInfo::where(['plan_type' => 'pro-annual-static'])->get();
			}
			if($subscription['plan_id'] =='business-quarterly-bundle-static' || $subscription['plan_id'] =='business-semi-bundle-static'){
				$mertedplan = MeterdPlanInfo::where(['plan_type' => 'business-month-static'])->get();
			}else if($subscription['plan_id'] =='business-annual-bundle-static' ){
				$mertedplan = MeterdPlanInfo::where(['plan_type' => 'business-annual-static'])->get();
			}
		}else{
		$mertedplan = MeterdPlanInfo::where(['plan_type' => $subscription['plan_id']])->get();
		}
		$items = [];
		$bundleItem = [];
		array_push($items, ['price' => $plan->id]);
		
		if (count($mertedplan) > 0) {
			foreach ($mertedplan as $merted) {
				if($subscription['bundle']){
					$bundleItem[] = ['price' => $merted->plan_id];
				}else{
				$items[] = ['price' => $merted->plan_id];
				}
			}
		}
		return ['items'=>$items,'bundleItem'=>$bundleItem,'mertedplan'=>$mertedplan];
	}
	/**
	 * @param $user_id
	 * @return JsonResponse
	 */
	public function startFreeForever($user_id) {
		$user = User::where('id', $user_id)->firstOrFail();

		$status = \App\Status::where('name', 'active')->first();
		// info(json_encode($user->currentPlan));die;
		$user->billing_status = 'VerifyRequired';
		$user->email_verified_token = Str::random(64);
		$user->status_id = $status->id;
		$customer = $this->createStripeCustomer($user);
		$user->stripe_id = $customer->id;
		$user->payment_method = 'stripe';
		$user->save();

		if (empty($user->currentPlan)) {

			$this->startFreeSubscription($user);
			/* $subscription = \App\Subscription::create();
				            $subscription->stripe_plan = 'free';
				            $subscription->name = "Free Forever";
				            $subscription->user_id = $user->id;
				            $subscription->trial_ends_at = null;
				            $subscription->ends_at = null;
			*/
		}

		$user->notify(new VerifyEmail($user));

		return response()->json(['success' => true, 'token' => $user->email_verified_token]);
	}

	/**
	 * @param $token
	 * @return \Illuminate\Routing\Redirector
	 */
	public function confirmEmailVerification(Request $request, $token) {
		$user = User::where('email_verified_token', $token)->first();
		if (!is_null($user) && $user->billing_status == 'VerifyRequired') {
			$user->billing_status = 'Active';
			$user->save();

		}
		Mail::send('/subscription/welcome', ['company' => Spark::$details,
			'full_name' => $user->full_name,
			'plan' => $user->currentPlan->stripe_plan,
			'base_url' => config('app.root_url'),
			'site_name' => config('app.site_url'),

		], function ($m) use ($user) {
			$m->from('accounts@bigcommand.com', 'Bigcommand Accounts');
			$m->to($user->email)->subject('[BigCommand] Welcome to Adilo cloud video hosting');
		});
		if ($request->ajax()) {
			return response()->json(['success' => true]);
		}

		return Redirect::to(config('app.site_url'));
	}
	/**
	 * Send reset password link notification
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function sendResetPasswordLink(Request $request) {
		$user = User::where('email', $request->input('email'))->first();
		if ($user && !is_null($user)) {
			if ($user->billing_status == 'Cancelled' || $user->billing_status == 'Failed') {
				return response()->json([
					'result' => 'cancelled',
				]);
			} else {
				$token = Password::getRepository()->create($user);

				$user->notify(new ResetPasswordLinkSent($token));

				return response()->json([
					'result' => 'success',
				]);
			}
		} else {
			return response()->json([
				'result' => 'error',
				'message' => "We couldn't find your " . config('app.name') . ' Account',
			]);
		}
	}

	/**
	 * Reset Password
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function setNewPasswordByToken(Request $request) {
		if ($request->has('token')) {
			$token = $request->input('token');
			if (!is_null($token)) {
				$email = '';
				$reset_list = DB::table('password_resets')->get();

				if ($reset_list) {
					foreach ($reset_list as $row) {
						if ($this->hasher->check($token, $row->token)) {
							$email = $row->email;
							break;
						}
					}
				}
				info($email);
				if ($email != '') {
					$user = User::where('email', $email)->first();
					if ($user) {
						$user->password = $this->hasher->make($request->input('password'));
						$user->save();

						$tokenObject = $this->createTokenForUser($user);

						DB::table('password_resets')->where('email', $user->email)->delete();

						$user = User::getUserDetails($user);

						return response()->json([
							'result' => 'success',
							'access_token' => $tokenObject->accessToken,
							'expires_in' => $tokenObject->token->expires_at->diffInSeconds(now()),
							'email' => $user->email,
							'userInfo' => $user,
						], 200);
					}
				}
			}
		}

		return response()->json('Invalid token. Please re-send email and get link again.', 422);
	}

	/**
	 * Get current subscription
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function getSubscription(Request $request, $id = false) {
		$user = $id ? User::where('id', $id)->first() : $request->user();

		$lastInvoice = Invoice::where('user_id', $user->id)
			->where('paid', 1)
			->orderBy('id', 'desc')
			->first();

		$subscription = $user->currentPlan;
		$plan = Spark::teamPlans()->where('id', $subscription->stripe_plan)->first();
		$stripePlan = SignupCoupon::where(['plan_id' => $plan->name, 'status' => 'active'])->first();

		$discount = (isset($stripePlan->amount) ? $stripePlan->amount : 0);
		if ($user->is_discvount_applied) {
			$plan->price = (float) $plan->price - (float) $discount;
		}
		if ($subscription) {
			return response()->json([
				'subscription' => $subscription,
				'last_bill_date' => isset($lastInvoice) ? $lastInvoice->updated_at->toDateTimeString() : ($subscription->updated_at ? $subscription->updated_at->toDateTimeString() : null),
				'plan' => $plan,
			]);
		}

		abort(404, 'Subscription not found');
	}
	function getProration(Request $request) {
		$proration_date = time();
		$user = $request->user();
		$subscription = $user->currentPlan;
		$cost = 0;
		$price = 0;
		$overusage = 0;
		$current_prorations = [];
		if ($request->input('type') == 'annual') {
			$plan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
			$annualplan = Spark::teamPlans()->where('id', str_replace('monthly', 'annual', $user->currentPlan->stripe_plan))->first();
			$newprice = $annualplan->price;
		} else {
			$plan = Spark::teamPlans()->where('id', $request->input('plan_id'))->first();
			$newprice = $plan->price;
		}
		$current_plan = Spark::teamPlans()->where('id', $subscription->stripe_plan)->first();
		$items = $this->createSubscriptionItems($user, $plan);
		if (strpos($user->currentPlan->stripe_plan, 'annual') !== false  || strpos($user->currentPlan->stripe_plan, 'bundle') !== false) {
			$anualRes = $this->calculateAnnualPropotion($request, $items, $plan);
			$price = $anualRes['price']/100;
			$overusage = $anualRes['overusage'];
			$cost = $newprice - $price;
		    $cost = ($cost > 0) ? $cost : 0;
			$cost = number_format($cost, 2, '.', '');
			$price = number_format($price, 2, '.', '');

			return ['current_prorations' => $current_prorations, 'due' => $cost, 'plan' => $current_plan, 'credit' => $price, 'overusage' => $overusage, 'cost' => $cost, 'invoice' => $anualRes, 'req' => []];

		} else {
			$invoice = \Stripe\Invoice::upcoming([
				'customer' => $user->stripe_id,
				'subscription' => $subscription->stripe_id,
				'subscription_items' => $items,
				'subscription_proration_date' => $proration_date,
			]);
			$priceCalculator = $this->priceAndOverageCalculator($invoice, $proration_date, $plan);
			$price = $priceCalculator['price'];
			$overusage = $priceCalculator['overusage'];
			$current_prorations = $priceCalculator['current_prorations'];
			$amount_charged = $priceCalculator['amount_charged'];

		}

		$price = $price / 100;
		// $price = $cost-(float)$current_plan->price;
		// $price = $plan->price - $amount_charged/100;
		$cost = $newprice - $price;
		$cost = ($cost > 0) ? $cost : 0;

		$price = number_format($price, 2, '.', '');
		return ['current_prorations' => $current_prorations, 'due' => $cost, 'plan' => $current_plan, 'credit' => $price, 'overusage' => $overusage, 'cost' => $cost, 'invoice' => $invoice, 'req' => [
			'customer' => $user->stripe_id,
			'subscription' => $subscription->stripe_id,
			'subscription_items' => $items,
			'subscription_proration_date' => $proration_date,
		]];

	}

	function calculateAnnualPropotion($request, $subitems, $plan) {
		$user = $request->user();
		$proration_date = time();
		$subscription = $user->currentPlan;
		/**/
		$subscription_items = SubscriptionItem::where('subscription_stripe_id', '!=', $user->currentPlan->stripe_id)->where('user_id', $user->id)->first();
		$mainItem = [];
		$items = $subitems;
		foreach ($items as $key => $item) {
			info($item['price'] . '==' . $request->input('plan_id'));
			if ($item['price'] == $request->input('plan_id')) {
				$mainItem = [$item];
				unset($items[$key]);
			}
		}
		$t = [];

		foreach ($items as $key => $item) {
			$t[] = $item;
		}
		$cost = 0;
		$price = 0;
		$overusage = 0;
		$current_prorations = [];
		$items = $t;
		// info(json_encode([
		// 	'customer' => $user->stripe_id,
		// 	'subscription' => $subscription_items->subscription_stripe_id,
		// 	'subscription_items' => $items,
		// 	'subscription_proration_date' => $proration_date,
		// ]));
		$invoice = \Stripe\Invoice::upcoming([
			'customer' => $user->stripe_id,
			'subscription' => $subscription_items->subscription_stripe_id,
			'subscription_items' => $items,
			'subscription_proration_date' => $proration_date,
		]);
 
		$priceCalculator = $this->priceAndOverageCalculator($invoice, $proration_date, $plan);
		$current_prorations = $priceCalculator['current_prorations'];
		$overusage = $priceCalculator['overusage'];
		$invoice = \Stripe\Invoice::upcoming([
			'customer' => $user->stripe_id,
			'subscription' => $subscription->stripe_id,
			'subscription_items' => $mainItem,
			'subscription_proration_date' => $proration_date,
		]);
		$priceCalculator = $this->priceAndOverageCalculator($invoice, $proration_date, $plan);
		$price = $priceCalculator['price'];
		//return ['overusage'=>$overusage,'price'=>$price,'current_prorations'=>$current_prorations,'amount_charged' => $invoice->amount_due];
		info(json_encode($invoice));
		return ['overusage' => $overusage, 'price' => $price, 'amount_charged' => $invoice->amount_due,'invoice'=>$invoice];

		/** */
	}
	function priceAndOverageCalculator($invoice, $proration_date, $plan) {
		$overusage = 0;
		$price = 0;
		$current_prorations = [];
		foreach ($invoice->lines->data as $line) {
			if ($line->period->start - $proration_date <= 1) {
				array_push($current_prorations, $line);
				if (isset($line->plan) && $line->plan->id != $plan->id) {
					$price += ($line->amount < 0) ? (-$line->amount) : $line->amount;
				}
			}
			if ($line->plan != null && $line->plan->usage_type == 'metered') {
				info($line->plan->id . '---');
				info('amountsss--' . (float) $line->amount);
				info('amount--' . (float) $line->amount / 100);

				$overusage = (float) $overusage + ((float) $line->amount / 100 * $line->quantity);
			}
		}
		//$overusage =  $this->getmertedOverageCost();
		return ['overusage' => $overusage, 'price' => $price, 'current_prorations' => $current_prorations, 'amount_charged' => $invoice->amount_due];
	}
	function getmertedOverageCost() {
		$month = date('m');
		$year = date('Y');
		$res = DB::select("SELECT * FROM user_over_usage WHERE YEAR(created_at) = $year AND MONTH(created_at) = $month");
		$total = 0;
		if (count($res) > 0) {
			foreach ($res as $val) {
				$stripe_id = $val->stripe_id;
				$pinfo = MeterdPlanInfo::where('plan_id', $stripe_id)->first();
				$price = $pinfo->unit_price;
				$price = (float) $price * (float) $val->over_usage;
				$total = $price + $total;
			}
		}
		return $total;
	}
	public function getPaymentAdjustment(Request $request, $id) {
		$this->validate($request, [
			'plan_id' => 'required|string|max:191',
			'start_date' => 'date',
		]);

		$user = User::where('id', $id)->first();
		$plan = Spark::teamPlans()->where('id', $request->input(['plan_id']))->first();
	}

	public function changeSubscription(Request $request, $id = false) {
		$this->validate($request, [
			'plan_id' => 'required|string|max:191',
			'start_date' => 'date',
		]);
		$stripe_request = [
			'proration_behavior' => 'always_invoice',
			'billing_cycle_anchor' => 'now',
		];
		DB::connection()->enableQueryLog();
		$user = $id ? User::where('id', $id)->first() : $request->user();
		$plan = Spark::teamPlans()->where('id', $request->input('plan_id'))->first();

		$trailEnd = 'now';
		if ($user->billing_address == 'Trail') {
			//$trailEnd = Carbon::parse($user->currentPlan->trial_ends_at)->timestamp;
		}
		if (!$plan) {
			return abort(404, 'Plan not found');
		}
		if (strpos($plan->id, 'annual') !== false) {
			info($plan->id);
			info('....');
			$currentPlan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
			$subscription = $this->swtichToAnnual($plan, $user, $currentPlan);
			return $subscription;
		} else {
			$currentPlanname = $user->currentPlan->stripe_plan;
			$currentPlanname = ucwords(str_replace('-', ' ', $currentPlanname));
			$currentPlan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
			$newPlanName = $request->input('plan_id');
			$newPlanName = ucwords(str_replace('-', ' ', $newPlanName));
			$trail_ends_at = date('Y-m-d', strtotime($user->currentPlan->trial_ends_at));
			$today = date('Y-m-d');
			// Calculating discount
			if ($user->currentPlan->stripe_plan === "free") {
				$credit = 0;
			} elseif ($currentPlan->price > $plan->price) {
				$credit = 0;
			} else {
				$now = Carbon::now();
				$updated_at = Carbon::parse($user->currentPlan->updated_at);
				$ends_at = Carbon::parse($user->currentPlan->ends_at);
				$daysInInterval = $ends_at->diffInDays($updated_at);
				if (!$daysInInterval) {
					$daysInInterval = 30;
				}
				$daysRemain = $ends_at->diffInDays($now);
				$credit = ($daysRemain * $currentPlan->price) / $daysInInterval;
				$fee_due_today = $plan->price - $credit;
				$credit = intval($credit);
				if ($trail_ends_at > $today) {
					$credit = 0;
				}
			}
			info('44444');
			info($user->payment_method);
			if ($user->payment_method === 'stripe') {
				//\Stripe\Stripe::setApiKey(config('services.stripe.secret'));

				if ($credit) {
					info('44444');
					$coupon = \Stripe\Coupon::create([
						'duration' => 'once',
						'amount_off' => $credit,
						'currency' => "USD",
					]);
				}
				$subscriptionCur = $user->currentPlan;
				$subscription = $user->subscription($user->currentPlan->name);
				if ($credit || $trail_ends_at > $today) {
					info('ggg');
					if ($trail_ends_at < $today) {
						// $subscription->swap($plan->id,[
						// 	'coupon' => $coupon->id,
						// ]);
						info($subscriptionCur->stripe_id);
						$this->checkYearly($user, $plan);
					 if(strpos($user->currentPlan->stripe_plan, 'bundle') !== false){
						$update_subscription = $this->updateSubscriptionfrombundleplan($user, $plan, $stripe_request, $coupon);
					 }else{
						$update_subscription = $this->updateSubscription($user, $plan, $stripe_request, $coupon);
					 }
						

					} else {
						info('gggjj');

						// $subscription->swap($plan->id);
						// $update_subscription = \Stripe\Subscription::update($subscriptionCur->stripe_id , [
						// 	'cancel_at_period_end' => false,
						// 	'proration_behavior' => 'create_prorations',
						// 	'items' => $subItems,
						// 	'trial_end'=>$trailEnd
						//   ]);

						//   $this->updateSubscriptionItems($plan,$user,$update_subscription);
						if(strpos($user->currentPlan->stripe_plan, 'bundle') !== false){
							$update_subscription = 	$this->updateSubscriptionfrombundleplan($user, $plan, $stripe_request);
						 }else{
						$update_subscription = $this->updateSubscription($user, $plan, $stripe_request);
						 }
					}
					// Invoice::create([
					//     "user_id" => $user->id,
					//     "provider_id" => "stripe",
					//     "plan_id" => $plan->id,
					//     "total" => $plan->price - $credit,
					//     "subscription_id" => $subscriptionCur->id,
					//     "status" => "paid",
					//     "system_name" => "stripe",
					// ]);

				} elseif ($currentPlan->price > $plan->price) {
					info('gggyyy');

					// $subscription->swap($plan->id);
					// $update_subscription = \Stripe\Subscription::update($subscriptionCur->stripe_id , [
					// 	'cancel_at_period_end' => false,
					// 	'proration_behavior' => 'create_prorations',
					// 	'items' => $subItems,
					// 	'trial_end'=>$trailEnd
					//   ]);

					//   $this->updateSubscriptionItems($plan,$user,$update_subscription);
					info($user->currentPlan->stripe_plan);
					if(strpos($user->currentPlan->stripe_plan, 'bundle') !== false){
						$update_subscription = 	$this->updateSubscriptionfrombundleplan($user, $plan, $stripe_request);
					 }else{
					$update_subscription = $this->updateSubscription($user, $plan, $stripe_request);
					 }

				} else {
					//$subscription->swapAndInvoice($plan->id);
					info('$plan->plan_id');
					info($plan->plan_id);
					// $update_subscription = \Stripe\Subscription::update($subscriptionCur->stripe_id , [
					// 	'cancel_at_period_end' => false,
					// 	'proration_behavior' => 'always_invoice',
					// 	'items' => $subItems,
					// 	'trial_end'=>$trailEnd
					//   ]);

					// $this->updateSubscriptionItems($plan,$user,$update_subscription);
					if(strpos($user->currentPlan->stripe_plan, 'bundle') !== false){
						$update_subscription = 	$this->updateSubscriptionfrombundleplan($user, $plan, $stripe_request, null, 'yes');
					 }else{
					$update_subscription = $this->updateSubscription($user, $plan, $stripe_request, null, 'yes');
					 }
					// Invoice::create([
					//     "user_id" => $user->id,
					// 	"provider_id" => "stripe",
					// 	"plan_id" => $plan->id,
					//     "total" => $plan->price - $credit,
					//     "subscription_id" => $subscriptionCur->id,
					//     "status" => "paid",
					//     "system_name" => "stripe",

					// ]);
				}
				info('json_encode($update_subscription)');
				info(json_encode($update_subscription));
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
				// Send mail notification
				Mail::send('/subscription/subscription_changed', [
					'full_name' => $user->full_name,
					'fee_due_today' => $plan->price - $credit,
					'current_plan_name' => $currentPlanname,
					'new_plan_name' => $newPlanName,
					'next_rebill_date' => date('M d, Y', strtotime($interval)),
					'new_plan_subscription_fee' => $plan->price,
					'plan' => $plan,
					'base_url' => config('app.root_url'),
					'site_name' => config('app.site_url'),
				], function ($m) use ($user) {
					$m->to($user->email)->subject('Your Adilo subscription plan has been changed');
				});

				addToLog(['user_id' => $request->user()->id,
					'activity_type' => 'changed_subscription',
					'subject' => "Changed subscription from: <span class='activity-content'>$currentPlanname</span> to <span class='activity-content'>$updatedPlan</span>",
				]);
				info(json_encode(DB::getQueryLog()));
				return $subscriptionCur;
			} else {
				$gateway = new Braintree_Gateway(config('services.braintree'));
				$token = $gateway->customer()->find($user->braintree_id)->paymentMethods[0]->token;
				$user->cancelSubscriptionPayPal();

				$new_sub = $gateway->subscription()->create([
					'paymentMethodToken' => $token,
					'planId' => $plan->id,
					'trialPeriod' => false,
					"price" => ($plan->price - $credit),
				]);

				$interval = $plan->interval === 'monthly' ? '+1 month' : '+1 year';

				$subscription = $user->currentPlan;
				$subscription->stripe_id = $new_sub->subscription->id;
				$subscription->stripe_plan = $plan->id;
				$subscription->trial_ends_at = null;
				$subscription->ends_at = date('Y-m-d H:i:s', strtotime($interval));
				$subscription->name = $plan->name;
				$subscription->save();
				$user->billing_status = 'Active';
				$user->trial_ends_at = null;
				$user->save();
				$updatedPlan = $user->currentPlan->name;
				addToLog(['user_id' => $request->user()->id,
					'activity_type' => 'changed_subscription',
					'subject' => "Changed subscription from: <span class='activity-content'>$currentPlanname</span> to <span class='activity-content'>$updatedPlan</span>",
				]);

				// Send mail notification
				Mail::send('/subscription/subscription_changed', [
					'full_name' => $user->full_name,
					'fee_due_today' => $plan->price - $credit,
					'current_plan_name' => $currentPlanname,
					'new_plan_name' => $newPlanName,
					'next_rebill_date' => date('M d, Y', strtotime($interval)),
					'new_plan_subscription_fee' => $plan->price,
					'plan' => $plan,
					'base_url' => config('app.root_url'),
					'site_name' => config('app.site_url'),
				], function ($m) use ($user) {
					$m->to($user->email)->subject('Your Adilo subscription plan has been changed');
				});
				info(json_encode(DB::getQueryLog()));
				return $subscription;
			}
		}

	}

	public function changeSubscriptionToAnnual(Request $request) {
		$user = $request->user();

		if ($user->currentPlan->interval === 'annual') {
			return abort(403, 'Already using annual');
		}

		$currentPlan = Spark::teamPlans()->where('id', str_replace('monthly', 'annual', $user->currentPlan->stripe_plan))->first();
		$plan = Spark::teamPlans()->where('id', str_replace('monthly', 'annual', $user->currentPlan->stripe_plan))->first();

		if (!$plan) {
			return abort(404, 'Plan not found');
		}
		$subscription = $this->swtichToAnnual($plan, $user, $currentPlan);
		return $subscription;
	}
	/**
	 *
	 */
	function swtichToAnnual($plan, $user, $currentPlan) {

		// Calculating discount
		$now = Carbon::now();
		$updated_at = Carbon::parse($user->currentPlan->updated_at);
		$ends_at = Carbon::parse($user->currentPlan->ends_at);
		info(json_encode($currentPlan->price));
		info(json_encode($plan));
		$daysInInterval = $ends_at->diffInDays($updated_at);
		$daysRemain = $ends_at->diffInDays($now);
		$credit = ($daysRemain * $currentPlan->price) / $daysInInterval;
		$fee_due_today = $plan->price - $credit;
		$credit = intval($credit);
		info('.....'.$user->payment_method);
		info($credit);
		if ($user->payment_method === 'stripe') {
			// Stripe::setApiKey(config('services.stripe.secret'));
			$subscriptionCur = $user->currentPlan;
			
			
			// $subscription = $user
			//     ->subscription($user->currentPlan->name, [
			//         'coupon' => $coupon->id,
			//     ])
			//     ->skipTrial()
			//     ->swap($plan->id);
			$subItems = $this->createSubscriptionItemsForAnnaul($user, $plan);
			$requu =  [
				'items' => $subItems['main'],
				'trial_end' => 'now',
				
			];
			if($credit>0){
				$coupon = \Stripe\Coupon::create([
				'duration' => 'once',
				"currency" => "usd",
				'amount_off' => $credit,
			]); 
			$requu['coupon'] = $coupon->id;
			}
			$this->deletesubscriptionItem($subItems['olditem']);
			$update_subscription = \Stripe\Subscription::update($subscriptionCur->stripe_id,$requu);

			UserSubscriptions::where(['user_id' => $user->id, 'stripe_id' => $subscriptionCur->stripe_id])->update(['stripe_id' => $update_subscription->id,'stripe_plan'=>$plan->id]);
			$newsubscription = \Stripe\Subscription::create([
				'customer' => $user->stripe_id,
				'items' => $subItems['newitem'],
				"trial_end" => 'now',
				//"default_payment_method"=>$tokenData['id']

			]);
			info(json_encode($newsubscription));
			UserSubscriptions::where(['user_id' => $user->id])->where('stripe_id', '!=', $update_subscription->id)
				->delete();
			UserSubscriptions::insert(['user_id' => $user->id, 'stripe_id' => $newsubscription->id,'stripe_plan'=>$plan->id]);

			array_push($newsubscription->items->data, $update_subscription->items->data[0]);
			$this->updateSubscriptionItems($plan, $user, $newsubscription);

			$interval = '+1 year';
			$subscriptionCur->ends_at = date('Y-m-d H:i:s', strtotime($interval));
			$subscriptionCur->name = $plan->name;
			$subscriptionCur->stripe_plan = $plan->id;
			$subscriptionCur->save();

			// Send mail notification
			Mail::send('/subscription/subscription_changed', [
				'full_name' => $user->full_name,
				'fee_due_today' => $fee_due_today,
				'current_plan_name' => $currentPlan->name . ' Monthly',
				'new_plan_name' => $currentPlan->name . 'Annual',
				'next_rebill_date' => date('M d, Y', strtotime($interval)),
				'new_plan_subscription_fee' => $plan->price,
				'plan' => $plan,
				'base_url' => config('app.root_url'),
				'site_name' => config('app.site_url'),
			], function ($m) use ($user) {
				$m->to($user->email)->subject('Your Adilo subscription plan has been changed');
			});

			return $subscriptionCur;
		} else {
			$gateway = new Braintree_Gateway(config('services.braintree'));
			$token = $gateway->customer()->find($user->braintree_id)->paymentMethods[0]->token;
			$user->cancelSubscriptionPayPal();

			$new_sub = $gateway->subscription()->create([
				'paymentMethodToken' => $token,
				'planId' => $plan->id,
				'trialPeriod' => false,
			]);

			$interval = '+1 year';

			$subscription = $user->currentPlan;
			$subscription->stripe_id = $new_sub->subscription->id;
			$subscription->stripe_plan = $plan->id;
			$subscription->name = $plan->name;
			$subscription->trial_ends_at = null;
			$subscription->ends_at = date('Y-m-d H:i:s', strtotime($interval));
			$subscription->save();

			$user->billing_status = 'Active';
			$user->trial_ends_at = null;
			$user->save();

			return $subscription;
		}
	}
	/**
	 * @param $user_id
	 * @return JsonResponse
	 */
	public function socialRegstartFreeForever($user_id) {
		$user = User::where('id', $user_id)->firstOrFail();
		$status = \App\Status::where('name', 'active')->first();

		$user->billing_status = 'Active';
		$user->email_verified_token = Str::random(64);
		$user->status_id = $status->id;
		$user->save();

		if (empty($user->currentPlan)) {
			$subscription = \App\Subscription::create();
			$subscription->stripe_plan = 'free';
			$subscription->name = "Free Forever";
			$subscription->user_id = $user->id;
			$subscription->trial_ends_at = null;
			$subscription->ends_at = null;
			$subscription->save();
		}

		return response()->json(['success' => true, 'token' => $user->email_verified_token]);
	}
	//Socail Register
	function commentSocialReg(Request $request) {
		$id = $request->id;
		$user = User::where($request->type . '_id', $id)->first();
		if ($user != null) {
			$auth = $this->socialLogin($request, $user);

			return response()->json($auth, 200);
		} else {
			$type = ($request->type == 'facebook') ? 'Facebook' : 'Google';
			if ($request->email != '') {
				$user = User::where('email', $request->email)->first();
				if ($user != null) {
					$auth = $this->socialLogin($request, $user);
					return response()->json($auth, 200);
				} else {
					$auth = $this->socialRegister($request);
					$this->notifyCommentRegister($auth, $type);
					return response()->json($auth, 200);
				}
			}
			$auth = $this->socialRegister($request);
			$this->notifyCommentRegister($auth, $type);
			return response()->json($auth, 200);
		}
	} //Socail Register
	function socialReg(Request $request) {
		$id = $request->id;
		if ($request->has('id') && $request->input('id') != '') {
			$user = User::where($request->type . '_id', $id)->first();
			if ($user != null) {
				return response()->json(['type' => 'error', 'msg' => 'Already registered'], 200);
			} else {
				if ($request->email != '') {
					$user = User::where('email', $request->email)->first();
					if ($user != null) {
						$auth = $this->socialLogin($request, $user);
						return response()->json($auth, 200);
					} else {
						$auth = $this->socialRegister($request);

						return response()->json($auth, 200);
					}
				}
				$auth = $this->socialRegister($request);
				return response()->json($auth, 200);
			}
		}
		return response()->json(['response' => 'error'], 404);
	}
	function notifyCommentRegister($auth, $type) {
		$user = User::find($auth['id']);
		$res = $user->notify(new SocialRegisterMail($user, $type));
	}
	//Socail Auth
	function socialAuth(Request $request) {
		$doLogin = false;
		if ($request->has('id') && $request->input('id') != '') {
			$id = $request->id;
			$user = User::where($request->type . '_id', $id)->first();
			if ($user != null) {
				$auth = $this->socialLogin($request, $user);
				$auth['is_new'] = 'no';
				return response()->json($auth, 200);
			} else {
				if ($request->email != '') {
					$user = User::where('email', $request->email)->first();
					if ($user != null) {
						$auth = $this->socialLogin($request, $user);
						$auth['is_new'] = 'no';
						return response()->json($auth, 200);
					} else {
						$auth = $this->socialRegister($request);
						//$auth->is_new = 'yes';
						$auth['is_new'] = 'no';
						return response()->json($auth, 200);
					}
				}
				$auth = $this->socialRegister($request);
				$auth['is_new'] = 'yes';
				return response()->json($auth, 200);
			}
		}
		return response()->json(['response' => 'error'], 404);
		/*
				  if($request->type=='facebook'){
				$id =  $request->id;
				$user = User::where('facebook_id',$id)->first();
				if($user!=null){
					$doLogin = true;
				}
			}
			if($request->type=='gmail'){
				$id =  $request->id;
				$user = User::where('gmail_id',$id)->first();
				if($user!=null){
					$doLogin = true;
				}
			}
			if($request->type=='twitter'){
				$id =  $request->id;
				$user = User::where('twitter_id',$id)->first();
				if($user!=null){
					$doLogin = true;
				}
			}
		*/

	}
	function socialLogin($request, $user) {

		$tokenObject = $this->createTokenForUser($user);

		$user->last_activity = date('Y-m-d H:i:s');
		$user->save();
		$this->setLoginActivity($request, $user);

		Stage::createDefaultStage($user);
		addToLog(['user_id' => $user->id, 'activity_type' => 'login', 'subject' => 'Logged in to account
			', ]);
		return [
			'access_token' => $tokenObject->accessToken,
			'expires_in' => $tokenObject->token->expires_at->diffInSeconds(now()),
			'userInfo' => $user,
		];

	}
	function socialRegister($request) {
		$user = new User();
		if ($request->type == 'facebook') {
			$user->facebook_id = $request->id;
		}
		if ($request->type == 'gmail') {
			$user->gmail_id = $request->id;
		}

		$email = $request->email;
		$name = $request->name;
		$timezone = $request->timezone ?? config('app.timezone');
		$status = \App\Status::where('name', 'active')->first();
		$user->password = $this->hasher->make($request->email);
		$user->email = $email;
		$user->name = $name;
		$user->photo_url = $request->photo_url;
		$user->trial_ends_at = now()->addDays(config('services.subscription.trial_duration'));
		$user->billing_status = 'Inactive';
		$user->status_id = $status->id;
		$user->save();
		$this->setLoginActivity($request, $user);

		UserSettings::createDefaultSettings($user, $timezone);

		$tokenObject = $this->createTokenForUser($user);

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

		return [
			'access_token' => $tokenObject->accessToken,
			'expires_in' => $tokenObject->token->expires_at->diffInSeconds(now()),
			'email' => $user->email,
			'name' => $user->name,
			'id' => $user->id,
			'userInfo' => $user,
			'result' => 'success',
		];

	}

	public function ownerPlan(Request $r) {
		$user = auth()->user();
		$spark = false;
		if ($user) {
			$userData = User::find($user->id);
			if ($userData) {
				$sub = $userData->currentPlan;
				if ($sub) {
					$spark = Spark::teamPlans()->where('id', $sub->stripe_plan)->first();
				}
			}
		}
		return $spark ?: 'failed';
	}
	function createUsage(Request $request) {
		$user = $request->user();
		$subscription_id = $user->currentPlan->id;die;
		$subscriptionItem = SubscriptionItem::where(['stripe_plan' => 'starter-monthly-metered-enriched-contacts', 'subscription_id' => $subscription_id])->first();
		info($subscriptionItem->stripe_id);
		/*$stripe = new \Stripe\StripeClient(
				'sk_test_NSppvefSmVOAxHZGfxzRs7Rj00QZi7MApW'
			  );
			  $stripe->subscriptionItems->createUsageRecord(
				'si_HYU7QqPW3whdyx',
				['quantity' => 100, 'timestamp' => 1571252444]
		*/
		$stripe = new StripeHelper();
		$res = $stripe->createUsageRecord($subscriptionItem->stripe_id, ['quantity' => 10, 'timestamp' => Carbon::now()->timestamp]);
		response()->json($res);

	}

	/**
	 * subscription items
	 */
	function createSubscriptionItems($user, $plan) {
		$subscription_items = $user->currentPlan->stripe_plan;
		$newplanitem = '';
		$oldplanitem = '';
		if ($plan->id == 'starter-annual-static') {
			$newplanitem = 'starter-yearly';
		}if ($plan->id == 'starter-monthly-static') {
			$newplanitem = 'starter-monthly';
		}if ($plan->id == 'pro-annual-static') {
			$newplanitem = 'pro-yearly';
		}if ($plan->id == 'pro-monthly-static') {
			$newplanitem = 'pro-monthly';
		}if ($plan->id == 'business-annual-static') {
			$newplanitem = 'business-year';
		}if ($plan->id == 'business-monthly-static') {
			$newplanitem = 'business-monthly';
		}if ($plan->id == 'free') {
			$newplanitem = 'free';
		}
		if ($plan->id == 'pro-quarterly-bundle-static' || $plan->id == 'pro-semi-bundle-static') {
			$newplanitem = 'pro-monthly';
		}if ($plan->id == 'pro-annual-bundle-static') {
			$newplanitem = 'pro-yearly';
		}if ($plan->id == 'business-quarterly-bundle-static' || $plan->id == 'business-semi-bundle-static') {
			$newplanitem = 'business-monthly';
		}
		if ($plan->id == 'business-annual-bundle-static') {
			$newplanitem = 'business-year';
		}

		if ($user->currentPlan->stripe_plan == 'starter-annual-static') {
			$oldplanitem = 'starter-yearly';
		}if ($user->currentPlan->stripe_plan == 'starter-monthly-static') {
			$oldplanitem = 'starter-monthly';
		}if ($user->currentPlan->stripe_plan == 'pro-annual-static') {
			$oldplanitem = 'pro-yearly';
		}if ($user->currentPlan->stripe_plan == 'pro-monthly-static') {
			$oldplanitem = 'pro-monthly';
		}if ($user->currentPlan->stripe_plan == 'business-annual-static') {
			$oldplanitem = 'business-year';
		}if ($user->currentPlan->stripe_plan == 'business-monthly-static') {
			$oldplanitem = 'business-monthly';
		}if ($user->currentPlan->stripe_plan == 'free') {
			$oldplanitem = 'free';
		}if ($user->currentPlan->stripe_plan == 'pro-quarterly-bundle-static' || $user->currentPlan->stripe_plan == 'pro-semi-bundle-static') {
			$oldplanitem = 'pro-monthly';
		}if ($user->currentPlan->stripe_plan == 'pro-annual-bundle-static') {
			$oldplanitem = 'pro-yearly';
		}
		if ($user->currentPlan->stripe_plan == 'business-quarterly-bundle-static' || $user->currentPlan->stripe_plan == 'business-semi-bundle-static') {
			$oldplanitem = 'business-monthly';
		}
		if ($user->currentPlan->stripe_plan == 'business-annual-bundle-static') {
			$oldplanitem = 'business-year';
		}
		$res = SubscriptionItem::where('subscription_id', $user->currentPlan->id)->get();
		$subscriptionItem = [];

		foreach ($res as $val) {
			$subscriptionItem[$val->stripe_plan] = $val->stripe_id;
		}
		info(json_encode($subscriptionItem));
		
		if (strpos($user->currentPlan->stripe_plan, 'paykickstart') !== false) {
			
			$item = [
				[
					'id' => $subscriptionItem[$user->currentPlan->stripe_plan],
					'price' => $plan->id,
				],
				[
					'price' => $newplanitem . '-metered-anti-piracy',
				],
				[
					'price' => $newplanitem . '-metered-bandwidth',
				],
				[
					'price' => $newplanitem . '-metered-captions',
				],
				[
					'price' => $newplanitem . '-metered-dynamic-watermark',
				],
				[
					'price' => $newplanitem . '-metered-enriched-contacts',
				],
				[
					'price' => $newplanitem . '-metered-forensic-watermark',
				],
				[
					'price' => $newplanitem . '-metered-translations',
				],
			];
		}else{
		$item = [
					[
						'id' => $subscriptionItem[$user->currentPlan->stripe_plan],
						'price' => $plan->id,
					],
					[
						'id' => $subscriptionItem[$oldplanitem . '-metered-anti-piracy'],
						'price' => $newplanitem . '-metered-anti-piracy',
					],
					[
						'id' => $subscriptionItem[$oldplanitem . '-metered-bandwidth'],
						'price' => $newplanitem . '-metered-bandwidth',
					],
					[
						'id' => $subscriptionItem[$oldplanitem . '-metered-captions'],
						'price' => $newplanitem . '-metered-captions',
					],
					[
						'id' => $subscriptionItem[$oldplanitem . '-metered-dynamic-watermark'],
						'price' => $newplanitem . '-metered-dynamic-watermark',
					],
					[
						'id' => $subscriptionItem[$oldplanitem . '-metered-enriched-contacts'],
						'price' => $newplanitem . '-metered-enriched-contacts',
					],
					[
						'id' => $subscriptionItem[$oldplanitem . '-metered-forensic-watermark'],
						'price' => $newplanitem . '-metered-forensic-watermark',
					],
					[
						'id' => $subscriptionItem[$oldplanitem . '-metered-translations'],
						'price' => $newplanitem . '-metered-translations',
					],
				];
			}
		
		
	return $item;
	}
	/**
	 * subscription items
	 */
	function createSubscriptionItemsForAnnaul($user, $plan) {
		$subscription_items = $user->currentPlan->stripe_plan;
		$newplanitem = '';
		$oldplanitem = '';
		if ($plan->id == 'starter-annual-static') {
			$newplanitem = 'starter-yearly';
		}if ($plan->id == 'starter-monthly-static') {
			$newplanitem = 'starter-monthly';
		}if ($plan->id == 'pro-annual-static') {
			$newplanitem = 'pro-yearly';
		}if ($plan->id == 'pro-monthly-static') {
			$newplanitem = 'pro-monthly';
		}if ($plan->id == 'business-annual-static') {
			$newplanitem = 'business-year';
		}if ($plan->id == 'business-monthly-static') {
			$newplanitem = 'business-monthly';
		}if ($plan->id == 'free') {
			$newplanitem = 'free';
		}if ($plan->id == 'pro-quarterly-bundle-static' || $plan->id == 'pro-semi-bundle-static' || $plan->id == 'pro-annual-bundle-static') {
			$oldplanitem = 'pro-monthly';
		}if ($plan->id == 'business-quarterly-bundle-static' || $plan->id == 'business-semi-bundle-static' || $plan->id == 'business-annual-bundle-static') {
			$oldplanitem = 'business-monthly';
		}
		
		if ($user->currentPlan->stripe_plan == 'starter-annual-static') {
			$oldplanitem = 'starter-yearly';
		}if ($user->currentPlan->stripe_plan == 'starter-monthly-static') {
			$oldplanitem = 'starter-monthly';
		}if ($user->currentPlan->stripe_plan == 'pro-annual-static') {
			$oldplanitem = 'pro-yearly';
		}if ($user->currentPlan->stripe_plan == 'pro-monthly-static') {
			$oldplanitem = 'pro-monthly';
		}if ($user->currentPlan->stripe_plan == 'business-annual-static') {
			$oldplanitem = 'business-year';
		}if ($user->currentPlan->stripe_plan == 'business-monthly-static') {
			$oldplanitem = 'business-monthly';
		}if ($user->currentPlan->stripe_plan == 'free') {
			$oldplanitem = 'free';
		}if ($user->currentPlan->stripe_plan == 'pro-quarterly-bundle-static' || $user->currentPlan->stripe_plan == 'pro-semi-bundle-static') {
			$oldplanitem = 'pro-monthly';
		}if ($user->currentPlan->stripe_plan == 'pro-annual-bundle-static') {
			$oldplanitem = 'pro-yearly';
		}
		if ($user->currentPlan->stripe_plan == 'business-quarterly-bundle-static' || $user->currentPlan->stripe_plan == 'business-semi-bundle-static' ) { 
			$oldplanitem = 'business-monthly';
		}
		if ($user->currentPlan->stripe_plan == 'business-annual-bundle-static') {
			$oldplanitem = 'business-year';
		}
		info('$plan->id--'.$plan->id);
		$res = SubscriptionItem::where('subscription_id', $user->currentPlan->id)->get();
		$subscriptionItem = [];
		
		foreach ($res as $val) {
			$subscriptionItem[$val->stripe_plan] = $val->stripe_id;
		}
		if (strpos($user->currentPlan->stripe_plan, 'paykickstart') !== false) {
			$olditem = [];
		}else{
		$olditem = [
			$subscriptionItem[$oldplanitem . '-metered-anti-piracy'],
			$subscriptionItem[$oldplanitem . '-metered-bandwidth'],
			$subscriptionItem[$oldplanitem . '-metered-captions'],
			$subscriptionItem[$oldplanitem . '-metered-dynamic-watermark'],
			$subscriptionItem[$oldplanitem . '-metered-enriched-contacts'],
			$subscriptionItem[$oldplanitem . '-metered-forensic-watermark'],
			$subscriptionItem[$oldplanitem . '-metered-translations'],
		];
	   }
		$mainplan = [
			'id' => $subscriptionItem[$user->currentPlan->stripe_plan],
			'price' => $plan->id,
		];

		$newitem = [
			[
				// 'id'=>$subscriptionItem[$oldplanitem.'-metered-anti-piracy'],
				'price' => $newplanitem . '-metered-anti-piracy',
			],
			[
				// 'id'=>$subscriptionItem[$oldplanitem.'-metered-bandwidth'],
				'price' => $newplanitem . '-metered-bandwidth',
			],
			[
				// 'id'=>$subscriptionItem[$oldplanitem.'-metered-captions'],
				'price' => $newplanitem . '-metered-captions',
			],
			[
				// 'id'=>$subscriptionItem[$oldplanitem.'-metered-dynamic-watermark'],
				'price' => $newplanitem . '-metered-dynamic-watermark',
			],
			[
				// 'id'=>$subscriptionItem[$oldplanitem.'-metered-enriched-contacts'],
				'price' => $newplanitem . '-metered-enriched-contacts',
			],
			[
				// 'id'=>$subscriptionItem[$oldplanitem.'-metered-forensic-watermark'],
				'price' => $newplanitem . '-metered-forensic-watermark',
			],
			[
				// 'id'=>$subscriptionItem[$oldplanitem.'-metered-translations'],
				'price' => $newplanitem . '-metered-translations',
			],
		];
		$mainplan = [
			'id' => $subscriptionItem[$user->currentPlan->stripe_plan],
			'price' => $plan->id,
		];
		return ['olditem' => $olditem, 'newitem' => $newitem, 'main' => [$mainplan]];
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

	function cancelSubscriptionItem($item) {
		$stripe = new StripeHelper();
		$stripe->cancelSubscriptionItem($item);
		UserSubscriptions::where('stripe_id', $item)->delete();

	}
	function deletesubscriptionItem($item) {
		$items = "'" . implode("','", $item) . "'";
		info(json_encode($item));
		// $subs = new \Stripe\SubscriptionItem();
		// $subs->delete('si_HZc77XgmzlaPwJ',['clear_usage'=>true]);die;
		$stripe = new StripeHelper();
		$stripe->deleteSubscriptionItem($item);
	}
	function updateSubscription($user, $plan, $stripe_request, $coupon = null, $invoice = null) {
		$subscriptionCur = $user->currentPlan;
		$subItems = $this->createSubscriptionItems($user, $plan);
		$trailEnd = 'now';
		$stripe_request['items'] = $subItems;
		$stripe_request['trial_end'] = $trailEnd;
		$stripe_request['cancel_at_period_end'] = false;

		if ($coupon != null && $invoice == null) {
			//$stripe_request['coupon'] = $coupon->id;
		}
		if ($invoice != null) {
			//	$stripe_request['proration_behavior'] = 'always_invoice';
		}

		try {
			$checkyearly = UserSubscriptions::where('user_id', $user->id)->where('stripe_id', '!=', $user->currentPlan->stripe_id)->first();
			if (strpos($user->currentPlan->stripe_plan, 'annual') !== false && $checkyearly != null) {
				$anuualitem = $this->createSubscriptionItemsForAnnaul($user, $plan);
				$this->cancelSubscriptionItem($checkyearly->stripe_id);
				$stripe_request['items'] = $anuualitem['newitem'];
				$stripe_request['items'][] = $anuualitem['main'][0];
			} else {

			}
			info('stripe_request->' . json_encode($stripe_request));
			$update_subscription = \Stripe\Subscription::update($subscriptionCur->stripe_id, $stripe_request);

			$this->updateSubscriptionItems($plan, $user, $update_subscription);
			UserSubscriptions::where(['user_id' => $user->id, 'stripe_id' => $subscriptionCur->stripe_id])->update(['stripe_id' => $update_subscription->id,'stripe_plan'=>$plan->id]);
			UserSubscriptions::where(['user_id' => $user->id])->where('stripe_id', '!=', $update_subscription->id)
				->delete();
			return $update_subscription;
		} catch (Exception $e) {
			return $e->getMessage();
		}

	}
	function updateSubscriptionfrombundleplan($user, $plan, $stripe_request, $coupon = null, $invoice = null) {
		$subscriptionCur = $user->currentPlan;
		info('updateSubscriptionfrombundleplan');
		$subItems = $this->createSubscriptionItems($user, $plan);
		$trailEnd = 'now';
		$stripe_request['items'] = $subItems;
		$stripe_request['trial_end'] = $trailEnd;
		$stripe_request['cancel_at_period_end'] = false;

		if ($coupon != null && $invoice == null) {
			//$stripe_request['coupon'] = $coupon->id;
		}
		if ($invoice != null) {
			//	$stripe_request['proration_behavior'] = 'always_invoice';
		}

		//try {
			$checkyearly = UserSubscriptions::where('user_id', $user->id)->where('stripe_id', '!=', $user->currentPlan->stripe_id)->first();
			if (strpos($user->currentPlan->stripe_plan, 'annual') !== false && $checkyearly != null) {
				$anuualitem = $this->createSubscriptionItemsForAnnaul($user, $plan);
				$this->cancelSubscriptionItem($checkyearly->stripe_id);
				$stripe_request['items'] = $anuualitem['newitem'];
				$stripe_request['items'][] = $anuualitem['main'][0];
			} else {

			}
			if (strpos($user->currentPlan->stripe_plan, 'bundle') !== false) {
				$checkbundle = UserSubscriptions::where('user_id', $user->id)->where('stripe_id', '!=', $user->currentPlan->stripe_id)->first();
				if($checkbundle != null){
				$bundleitem = $this->createSubscriptionItemsForAnnaul($user, $plan);
				info('bundleitem'.json_encode($bundleitem));
				$this->cancelSubscriptionItem($checkbundle->stripe_id);
				$stripe_request['items'] = $bundleitem['newitem'];
				$stripe_request['items'][] = $bundleitem['main'][0];
				}

			}
			info('stripe_request->' . json_encode($stripe_request));
			$update_subscription = \Stripe\Subscription::update($subscriptionCur->stripe_id, $stripe_request);

			$this->updateSubscriptionItems($plan, $user, $update_subscription);
			UserSubscriptions::where(['user_id' => $user->id, 'stripe_id' => $subscriptionCur->stripe_id])->update(['stripe_id' => $update_subscription->id,'stripe_plan'=>$plan->id]);
			UserSubscriptions::where(['user_id' => $user->id])->where('stripe_id', '!=', $update_subscription->id)
				->delete();
			return $update_subscription;
		// } catch (Exception $e) {
		// 	return $e->getMessage();
		// }

	}
	function checkYearly($user, $plan) {
		$checkyearly = UserSubscriptions::where('user_id', $user->id)->where('stripe_id', '!=', $user->currentPlan->stripe_id)->first();
		if (strpos($user->currentPlan->name, 'annual') !== false && $checkyearly != null) {
			$this->createSubscriptionItemsForAnnaul($user, $plan);
		}
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
	
	function startFreeSubscription($user) {

		$plan = Spark::teamPlans()->where('id', 'free')->first();
		$mertedplan = MeterdPlanInfo::where(['plan_type' => 'free'])->get();
		$items = [];
		array_push($items, ['price' => $plan->id]);
		$interval = $plan->interval === 'monthly' ? '+1 month' : '+1 year';

		if (count($mertedplan) > 0) {
			foreach ($mertedplan as $merted) {
				$items[] = ['price' => $merted->plan_id];
			}
		} 
		$new_subscription = \Stripe\Subscription::create([
			'customer' => $user->stripe_id, 
			 'items' => $items,

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
		info(json_encode($subscription));
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

	function cancelledUsers($id) {
		$user = User::where(['id' => $id, 'billing_status' => 'Cancelled'])->first();

		if ($user && !is_null($user)) {
			if ($user->billing_status == 'Cancelled') {
				$user->plan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
				$user->subscriptions = $user->subscriptions();
				$user->failed_invoice = FailedPayment::where('user_id', $user->id)->first();
				$user->plan->interval = ucwords($user->plan->interval);
				return response()->json(['type' => 'success', 'user' => $user]);
			}
		}
		return response()->json(['type' => 'error']);
	}

	function createSource($id, $token) {
		$user = User::where('id', $id)->first();
		\Stripe\Stripe::setApiKey(config('services.stripe.secret'));
		$customer = \Stripe\Customer::retrieve($user->stripe_id);
		$res2 = \Stripe\Token::retrieve($token['id']);
		/*
			$res = $customer->allSources(
				$user->stripe_id
			  );
			$isSource = false;
			$allSource = $res->data;
			$allfigerprint = [];
			$source = null;
			foreach($allSource as $val){
				// $allfigerprint[]=$val->fingerprint;
				info('tokenyyyy->'.json_encode($val));
				// info('allfingerprint->'.$res2->card->fingerprint);
				if($val->fingerprint==$res2->card->fingerprint){
					$isSource = true;
					$source = $val;

				}
			}
			 info('source->'.json_encode($source));
			//  info('customer->'.gettype($res2->card));die;

			if(!$isSource){
			$source =  $customer->createSource(
				$customer->id,
				['source' => $token['id']]
			);
			}
		*/
		$card = Card::where(['user_id' => $user->id, 'fingerprint' => $res2->card->fingerprint])->first();
		$card_ = $res2->card;
		if ($card == null) {
			$source = $customer->createSource(
				$customer->id,
				['source' => $token['id']]
			);
			info('token->' . json_encode($source));

			$card = Card::create([
				'user_id' => $user->id,
				'fingerprint' => $res2->card->fingerprint,
				'source_id' => $source->id,
				'exp_year' => $card_->exp_year,
				'exp_month' => $card_->exp_month,
				'card_brand' => $card_->brand,
				'card_last_four' => $card_->last4,
			]
			);
		}
		$customer = \Stripe\Customer::update($user->stripe_id, ['default_source' => $card->source_id]);

	}

	function payPendingInvoice(Request $request) {
		try {

			$stripeHelper = new StripeHelper();
			$subscription = $request->subscription;
			$tokenData = $request->tokenData;
			$card = $tokenData['card'];

			$user = User::where('id', $subscription['user_id'])->first();
			$source = $this->createSource($subscription['user_id'], $tokenData);
			$user->exp_year = $card['exp_year'];
			$user->exp_month = $card['exp_month'];
			$user->card_brand = $card['brand'];
			$user->card_last_four = $card['last4'];
			$stripe_invoice = \Stripe\Invoice::retrieve($subscription['invoice_id']);
			$res = $stripe_invoice->pay();

			$user->billing_status = 'Active';
			$user->save();

			return ['result' => 'success', 'response' => $res];
		} catch (Exception $e) {

			return ['result' => 'error', 'error' => $e->getMessage()];
		}

	}

	function createNewUser(Request $request) {
		$data = $request->all();
		$this->validate($request, [
			'first_name' => 'required|string|max:191',
			'email' => 'required|string|email|max:191|unique:users',

		]);
		$permitted_chars = config('app.permitted_chars');
		$password = isset($data['password'])
		? $validateData['password']
		: ucwords($this->passwordGenerated());
		$blocked = BlockedEmail::where('email', $request->email)->first();
		if ($blocked) {
			return response()->json([
				'success' => false,
				'result' => 'blocked',
			]);
		}

		$name = $request->first_name . ' ' . $request->last_name;
		$timezone = $request->timezone ?? config('app.timezone');
		$status = \App\Status::where('name', 'suspended')->first();
		$user = new User();
		$user->name = $name;
		$user->email = $request->email;
		$user->password = Hash::make($password);
		$user->billing_type = $data['billing_type'];
		$user->user_origin = 'from_admin';
		$user->admin_user_status = 'pending';
		$user->user_plan_id = $data['plan_id'];
		$user->user_token = $this->generateRandomString(20);
		$user->save();

		$this->setLoginActivity($request, $user);
		$ip = $request->getClientIp();

		UserSettings::createDefaultSettings($user, $timezone, $ip);

		$tokenObject = $this->createTokenForUser($user);

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
		if ($data['billing_type'] == 'by_admin') {
			$this->manageAdminUserSubscription($user->id, $data);
		}

		if ($data['password_type'] == 'automated') {
			Mail::send('/billing/updated', [
				'full_name' => $user->full_name,
				'old' => '',
				'name' => 'password',
				'new' => $password,

			], function ($m) use ($user) {
				$m->to($user->email)->subject('[Critical]Your Adilo profile has been updated')->from('support@bigcommand.com', 'BigCommand Support');;
			});
		}
		return response()->json([
			'success' => true,
			'result' => 'success',
			'password' => $password,
			'user' => $user,
		]);

	}

	function manageAdminUserSubscription($user_id, $data) {
		$user = User::find($user_id);
		$plan = Spark::teamPlans()->where('id', $data['plan_id'])->first();
		$user->billing_status = 'Trial';
		$status = \App\Status::where('name', 'active')->first();
		$cardData = $data['card'];
		$getCustomer = $this->createStripeCustomerWithSource($user, $cardData['token']);
		if ($getCustomer['result'] != 'success') {
			return $getCustomer;
		} else {
			$customer = $getCustomer['customer'];
		}
		$user->stripe_id = $customer->id;
		$user->trial_ends_at = Carbon::now()->addDays(config('services.subscription.trial_duration'));
		$user->payment_method = $data['payment_method'];
		$user->card_brand = isset($cardData['cardType']) ? $cardData['cardType'] : null;
		$user->card_last_four = isset($cardData['last4']) ? $cardData['last4'] : null;

		$user->exp_month = isset($cardData['exp_month']) ? $cardData['exp_month'] : null;
		$user->exp_year = isset($cardData['exp_year']) ? $cardData['exp_year'] : null;
		$user->status_id = $status->id;
		info(json_encode($user));
		$user->save();
		$mertedplan = MeterdPlanInfo::where(['plan_type' => $data['plan_id']])->get();
		$items = [];
		array_push($items, ['price' => $plan->id]);
		$interval = $plan->interval === 'monthly' ? '+1 month' : '+1 year';
		if (count($mertedplan) > 0) {
			foreach ($mertedplan as $merted) {
				$items[] = ['price' => $merted->plan_id];
			}
		}
		$new_subscription = \Stripe\Subscription::create([
			'customer' => $user->stripe_id,
			'items' => $items,
			"trial_end" => Carbon::now()->addDays(config('services.subscription.trial_duration'))->timestamp,
		]);
		$user->subscriptions()->create([
			'name' => $plan->name,
			'stripe_id' => $new_subscription->id,
			'stripe_plan' => $plan->id,
			'quantity' => 1,
			'trial_ends_at' => $user->trial_ends_at,
			'ends_at' => date('Y-m-d H:i:s', strtotime($interval . ' ' . config('services.subscription.trial_duration') . 'days')),
		]);
		$subscriptionitems = $new_subscription->items;
		foreach ($subscriptionitems->data as $row) {
			\App\SubscriptionItem::insert([
				'subscription_id' => $user->currentPlan->id,
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
		$user = User::getUserDetails($user);
		return $user;
	}

	function passwordGenerated($length = 10) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-=+?";
		$password = substr(str_shuffle($chars), 0, $length);
		return $password;
	}

	function getPartialUser(Request $request, $id) {
		$user = User::where(['admin_user_status' => 'pending', 'user_id', $id])->first();
		return response()->json([
			'result' => true,
			'user' => $user,
		]);

	}

	function resetUserPassword(Request $request, $token) {
		$user = User::where(['user_token' => $token])->first();
		//'admin_user_status'=>'pending'
		if ($user) {
			$user->password = Hash::make($request->input('password'));
			$user->admin_user_status = 'activated';
			if ($user->billing_type != 'by_admin') {
				$user->billing_status = 'Inactive';
			}
			$user->save();
			$tokenObject = $this->createTokenForUser($user);
			return response()->json([
				'result' => true,
				'access_token' => $tokenObject->accessToken,
				'expires_in' => $tokenObject->token->expires_at->diffInSeconds(now()),
				'email' => $user->email,
				'userInfo' => $user,

			]);
		}
		return response()->json([
			'result' => false,
			'error' => 'User does not exist',
		]);
	}

	function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	function getUserByID(Request $request, $id) {
		$user = User::where('id', $id)->first();
		$tokenObject = $this->createTokenForUser($user);
		// if (!Spark::createsAdditionalTeams()) {
		// 	abort(404);
		// }
		return response()->json([
			'access_token' => $tokenObject->accessToken,
			'expires_in' => $tokenObject->token->expires_at->diffInSeconds(now()),
			'email' => $user->email,
			'name' => $user->name,
			'id' => $user->id,
			'userInfo' => $user,
			'result' => 'success',
		], 200);
	}
	function getUserByCUSTID(Request $request, $id) {
		$user = User::where('stripe_id', $id)->first();
		$tokenObject = $this->createTokenForUser($user);
		// if (!Spark::createsAdditionalTeams()) {
		// 	abort(404);
		// }
		return response()->json([
			'access_token' => $tokenObject->accessToken,
			'expires_in' => $tokenObject->token->expires_at->diffInSeconds(now()),
			'email' => $user->email,
			'name' => $user->name,
			'id' => $user->id,
			'userInfo' => $user,
			'result' => 'success',
		], 200);
	}

	function addMember(Request $request) {
		$data = $request->all();
		$result = $this->mailChimpAddMember($data['email'], $data['tags']);
		return response()->json([
			'result' => 'success',
			'data' => $result,
		], 200);
	}

	function mailChimpAddMember($email, $tags) {
		$list_id = '73c621cef9';
		$subscriber_hash = MailChimp::subscriberHash($email);
		$result = $this->mailchimp->put("lists/$list_id/members/$subscriber_hash", [
			'email_address' => $email,
			'tags' => json_decode($tags, true),
			'status' => 'subscribed',
		]);
		$stripe = new StripeHelper();
		return $stripe->addMemberToActiveCapaign($email, json_decode($tags, true));
	}

	function getAdminUserProration(Request $request, $id) {
		$proration_date = time();
		$user = User::find($id);
		$subscription = $user->currentPlan;
		$cost = 0;
		$price = 0;
		$overusage = 0;
		$current_prorations = [];
		if ($request->input('type') == 'annual') {
			$plan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
			$annualplan = Spark::teamPlans()->where('id', str_replace('monthly', 'annual', $user->currentPlan->stripe_plan))->first();
			$newprice = $annualplan->price;
		} else {
			$plan = Spark::teamPlans()->where('id', $request->input('plan_id'))->first();
			$newprice = $plan->price;
		}
		$current_plan = Spark::teamPlans()->where('id', $subscription->stripe_plan)->first();
		$items = $this->createSubscriptionItems($user, $plan);
		if (strpos($user->currentPlan->stripe_plan, 'annual') !== false || strpos($user->currentPlan->stripe_plan, 'bundle') !== false) {
			$anualRes = $this->calculateAnnualPropotion($request, $items, $plan);
			return ['current_prorations' => $current_prorations, 'due' => $cost, 'plan' => $current_plan, 'credit' => $price, 'overusage' => $overusage, 'cost' => $cost, 'invoice' => $anualRes, 'req' => []];

		} else {
			$invoice = \Stripe\Invoice::upcoming([
				'customer' => $user->stripe_id,
				'subscription' => $subscription->stripe_id,
				'subscription_items' => $items,
				'subscription_proration_date' => $proration_date,
			]);
			$priceCalculator = $this->priceAndOverageCalculator($invoice, $proration_date, $plan);
			$price = $priceCalculator['price'];
			$overusage = $priceCalculator['overusage'];
			$current_prorations = $priceCalculator['current_prorations'];
			$amount_charged = $priceCalculator['amount_charged'];

		}

		$price = $price / 100;
		// $price = $cost-(float)$current_plan->price;
		// $price = $plan->price - $amount_charged/100;
		$cost = $newprice - $price;
		$cost = ($cost > 0) ? $cost : 0;

		$price = number_format($price, 2, '.', '');
		$cost = number_format($cost, 2, '.', '');
		return ['current_prorations' => $current_prorations, 'due' => $cost, 'plan' => $current_plan, 'credit' => $price, 'overusage' => $overusage, 'cost' => $cost, 'invoice' => $invoice, 'req' => [
			'customer' => $user->stripe_id,
			'subscription' => $subscription->stripe_id,
			'subscription_items' => $items,
			'subscription_proration_date' => $proration_date,
		]];

	}

	function updateUserSubscription(Request $request, $id) {

		$this->validate($request, [
			'plan_id' => 'required|string|max:191',
			'start_date' => 'date',
		]);
		$stripe_request = [
			'proration_behavior' => 'always_invoice',
			'billing_cycle_anchor' => 'now',
		];
		if($request->input('pay_type')=='manual_pay'){
			$stripe_request = [
				'proration_behavior' => 'always_invoice',
				'billing_cycle_anchor' => 'now',
			];
		} 
		if($request->input('date_type')=='endcycle'){
			$stripe_request = [
				'proration_behavior' => 'none',
				
			];
		} 
		
		DB::connection()->enableQueryLog();
		$user = User::where('id', $id)->first();
		$plan = Spark::teamPlans()->where('id', $request->input('plan_id'))->first();
		$trailEnd = 'now';
		if ($user->billing_address == 'Trail') {
			//$trailEnd = Carbon::parse($user->currentPlan->trial_ends_at)->timestamp;
		}
		if (!$plan) {
			return abort(404, 'Plan not found');
		}
		if (strpos($plan->id, 'annual') !== false) {
			$currentPlan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
			$subscription = $this->swtichToAnnual($plan, $user, $currentPlan);
			return $subscription;
		} else {
			$currentPlanname = $user->currentPlan->stripe_plan;
			$currentPlanname = ucwords(str_replace('-', ' ', $currentPlanname));
			$currentPlan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
			$newPlanName = $request->input('plan_id');
			$newPlanName = ucwords(str_replace('-', ' ', $newPlanName));
			$trail_ends_at = date('Y-m-d', strtotime($user->currentPlan->trial_ends_at));
			$today = date('Y-m-d');
			// Calculating discount
			if ($user->currentPlan->stripe_plan === "free") {
				$credit = 0;
			} elseif ($currentPlan->price > $plan->price) {
				$credit = 0;
			} else {
				$now = Carbon::now();
				$updated_at = Carbon::parse($user->currentPlan->updated_at);
				$ends_at = Carbon::parse($user->currentPlan->ends_at);
				$daysInInterval = $ends_at->diffInDays($updated_at);
				if (!$daysInInterval) {
					$daysInInterval = 30;
				}
				$daysRemain = $ends_at->diffInDays($now);
				$credit = ($daysRemain * $currentPlan->price) / $daysInInterval;
				$fee_due_today = $plan->price - $credit;
				$credit = intval($credit);
				if ($trail_ends_at > $today) {
					$credit = 0;
				}
			}
			if ($user->payment_method === 'stripe') {
				//\Stripe\Stripe::setApiKey(config('services.stripe.secret'));

				if ($credit) {
					info('44444');
					$coupon = \Stripe\Coupon::create([
						'duration' => 'once',
						'amount_off' => $credit,
						'currency' => "USD",
					]);
				}
				$subscriptionCur = $user->currentPlan;
				$subscription = $user->subscription($user->currentPlan->name);
				if ($credit || $trail_ends_at > $today) {
					info('ggg');
					if ($trail_ends_at < $today) {
						// $subscription->swap($plan->id,[
						// 	'coupon' => $coupon->id,
						// ]);
						info($subscriptionCur->stripe_id);
						$this->checkYearly($user, $plan);
						$update_subscription = $this->updateSubscriptionFromAdmin($user, $plan,$request, $stripe_request, $coupon);
					} else {
						$update_subscription = $this->updateSubscriptionFromAdmin($user, $plan,$request, $stripe_request);
					}
				} elseif ($currentPlan->price > $plan->price) {
					$update_subscription = $this->updateSubscriptionFromAdmin($user, $plan,$request, $stripe_request);
				} else {
					$update_subscription = $this->updateSubscriptionFromAdmin($user, $plan,$request, $stripe_request, null, 'yes');
				}
				info($update_subscription);
				UserSubscriptions::where(['user_id' => $user->id, 'stripe_id' => $subscriptionCur->stripe_id])->update(['stripe_id' => $update_subscription->id]);
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
				// Send mail notification
				Mail::send('/subscription/subscription_changed', [
					'full_name' => $user->full_name,
					'fee_due_today' => $plan->price - $credit,
					'current_plan_name' => $currentPlanname,
					'new_plan_name' => $newPlanName,
					'next_rebill_date' => date('M d, Y', strtotime($interval)),
					'new_plan_subscription_fee' => $plan->price,
					'plan' => $plan,
					'base_url' => config('app.root_url'),
					'site_name' => config('app.site_url'),
				], function ($m) use ($user) {
					$m->to($user->email)->subject('Your Adilo subscription plan has been changed');
				});

				addToLog(['user_id' => $request->user()->id,
					'activity_type' => 'changed_subscription',
					'subject' => "Changed subscription from: <span class='activity-content'>$currentPlanname</span> to <span class='activity-content'>$updatedPlan</span>",
				]);
				info(json_encode(DB::getQueryLog()));
				return $subscriptionCur;
			} else {
				$gateway = new Braintree_Gateway(config('services.braintree'));
				$token = $gateway->customer()->find($user->braintree_id)->paymentMethods[0]->token;
				$user->cancelSubscriptionPayPal();

				$new_sub = $gateway->subscription()->create([
					'paymentMethodToken' => $token,
					'planId' => $plan->id,
					'trialPeriod' => false,
					"price" => ($plan->price - $credit),
				]);

				$interval = $plan->interval === 'monthly' ? '+1 month' : '+1 year';

				$subscription = $user->currentPlan;
				$subscription->stripe_id = $new_sub->subscription->id;
				$subscription->stripe_plan = $plan->id;
				$subscription->trial_ends_at = null;
				$subscription->ends_at = date('Y-m-d H:i:s', strtotime($interval));
				$subscription->name = $plan->name;
				$subscription->save();
				$user->billing_status = 'Active';
				$user->trial_ends_at = null;
				$user->save();
				$updatedPlan = $user->currentPlan->name;
				// Send mail notification
				Mail::send('/subscription/subscription_changed', [
					'full_name' => $user->full_name,
					'fee_due_today' => $plan->price - $credit,
					'current_plan_name' => $currentPlanname,
					'new_plan_name' => $newPlanName,
					'next_rebill_date' => date('M d, Y', strtotime($interval)),
					'new_plan_subscription_fee' => $plan->price,
					'plan' => $plan,
					'base_url' => config('app.root_url'),
					'site_name' => config('app.site_url'),
				], function ($m) use ($user) {
					$m->to($user->email)->subject('Your Adilo subscription plan has been changed');
				});
				return $subscription;
			}
		}
	}

	function updateSubscriptionFromAdmin($user, $plan,$request, $stripe_request, $coupon = null, $invoice = null) {
		$subscriptionCur = $user->currentPlan;
		if($request->date_type!='today'){
			$subItems = $this->createSubscriptionScheduleItems($user, $plan);
		}else{
			$subItems = $this->createSubscriptionItems($user, $plan);
		}
		$trailEnd = 'now';
		//$stripe_request['items'] = $subItems;
		$stripe_request['trial_end'] = $trailEnd;
		$stripe_request['cancel_at_period_end'] = false;
		try {
			$checkyearly = UserSubscriptions::where('user_id', $user->id)->where('stripe_id', '!=', $user->currentPlan->stripe_id)->first(); 
			if (strpos($user->currentPlan->stripe_plan, 'annual') !== false && $checkyearly != null) {
				$anuualitem = $this->createSubscriptionItemsForAnnaul($user, $plan);
				$this->cancelSubscriptionItem($checkyearly->stripe_id);
				$stripe_request['items'] = $anuualitem['newitem'];
				$stripe_request['items'][] = $anuualitem['main'][0];
			} else {

			}
			
			$current_subscription = \Stripe\Subscription::retrieve($subscriptionCur->stripe_id);
			$phasestart = $current_subscription->current_period_end;
			$phasestart =  Carbon::now()->addSeconds(120)->timestamp;
			$stripe_request = ['customer'=>$user->stripe_id,
							   'start_date' => $phasestart,
							   'end_behavior' => 'release',
							   'phases' =>[
								   [
							   'plans' => $subItems,
							   ]
							   ]
							];
			info('stripe_requestddd->' . json_encode($stripe_request));	
			$stripeHelper = new StripeHelper();			
			$update_subscription = $stripeHelper->createSubscriptionSchedule($stripe_request);
			info('update_subscription->' . json_encode($update_subscription));
			// $this->updateSubscriptionItems($plan, $user, $update_subscription);
			// UserSubscriptions::where(['user_id' => $user->id, 'stripe_id' => $subscriptionCur->stripe_id])->update(['stripe_id' => $update_subscription->id]);
			// UserSubscriptions::where(['user_id' => $user->id])->where('stripe_id', '!=', $update_subscription->id)
			// 	->delete();

			$this->manageSubSchedule($plan,$update_subscription['result'],$user,$stripe_request,$request->date_type,$request->pay_type);
				die;
			return $update_subscription;
			
		} catch (Exception $e) {
			return $e->getMessage();
		}

	}

	function manageSubSchedule($plan,$update_subscription,$user,$stripe_request,$date_type,$pay_type){
		$cplan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
		info('----'.json_encode($user->currentPlan));
		info('--ggg--'.json_encode($cplan));
		$data = ['user_id'=>$user->id,'sub_schd_id'=>$update_subscription->id,'current_sub_id'=>$user->currentPlan->stripe_id,'current_plan_id'=>$user->currentPlan->stripe_plan,'upcoming_plan_id'=>$plan->id,'current_sub_type'=>$cplan->interval,'upcoming_sub_type'=>$plan->interval,'sub_start_timestamp'=>$stripe_request['start_date'],'status'=>'pending','pay_by'=>$pay_type,'schedule_type'=>$date_type];
		SubscriptionSchedule::insert($data);
	}
	/**
	 * subscription items
	 */
	function createSubscriptionScheduleItems($user, $plan) {
		$subscription_items = $user->currentPlan->stripe_plan;
		$newplanitem = '';
		$oldplanitem = '';
		if ($plan->id == 'starter-annual-static') {
			$newplanitem = 'starter-yearly';
		}if ($plan->id == 'starter-monthly-static') {
			$newplanitem = 'starter-monthly';
		}if ($plan->id == 'pro-annual-static') {
			$newplanitem = 'pro-yearly';
		}if ($plan->id == 'pro-monthly-static') {
			$newplanitem = 'pro-monthly';
		}if ($plan->id == 'business-annual-static') {
			$newplanitem = 'business-year';
		}if ($plan->id == 'business-monthly-static') {
			$newplanitem = 'business-monthly';
		}if ($plan->id == 'free') {
			$newplanitem = 'free';
		}
		
		$item = [
			[
				'price' => $plan->id,
			],
			[
				'price' => $newplanitem . '-metered-anti-piracy',
			],
			[
				'price' => $newplanitem . '-metered-bandwidth',
			],
			[
				'price' => $newplanitem . '-metered-captions',
			],
			[
				'price' => $newplanitem . '-metered-dynamic-watermark',
			],
			[
				'price' => $newplanitem . '-metered-enriched-contacts',
			],
			[
				'price' => $newplanitem . '-metered-forensic-watermark',
			],
			[
				'price' => $newplanitem . '-metered-translations',
			],
		];

		return $item;
	}
	function planById($id){
		$plan = Spark::teamPlans()->where('id', $id)->first();
		return response()->json($plan, 200);
	}
	function removeUser($id){
		User::where('stripe_id',$id)->delete();

	}

	function payStickRegister(Request $req){
		info('PayStick---'.json_encode($req));

	}

}
