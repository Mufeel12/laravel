<?php

namespace Laravel\Spark\Http\Controllers\Settings\Billing;

use Laravel\Spark\Spark;
use Illuminate\Http\Response;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Laravel\Spark\Events\Subscription\SubscriptionCancelled;
use Laravel\Spark\Contracts\Repositories\LocalInvoiceRepository;
use Laravel\Spark\Repositories\StripeLocalInvoiceRepository;
use Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled as TeamSubscriptionCancelled;
use Laravel\Spark\LocalInvoice as Invoice;
use Illuminate\Support\Facades\Mail;
use Log;
use Carbon\Carbon;
use App\SubscriptionSchedule;
use App\Subscription;
use App\FailedPayment;
use App\Http\Helpers\StripeHelper;
use App\SubscriptionItem;
use DB;
use App\User;
class StripeWebhookController extends WebhookController
{
    use SendsInvoiceNotifications;

    /**
     * Handle a successful invoice payment from a Stripe subscription.
     *
     * By default, this e-mails a copy of the invoice to the customer.
     *
     * @param  array  $payload
     * @return Response
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
       
        $subjects = config('app.mail_subjects');
        info('handleInvoicePaymentSucceeded->'.$payload['data']['object']['id']);
        info('handleInvoicePaymentSucceeded->'.json_encode($payload['data']['object']));
        $object = $payload['data']['object'];
        $user = $this->getUserByStripeId( $payload['data']['object']['customer']);
        // $user = $this->getUserByStripeId('cus_HbpwUXqUAOHRpi');
        info($payload['data']['object']['customer']);
        if(isset($user->currentPlan)){
        $plan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
            info('handleInvoicePaymentSucceeded_usert'.json_encode($user));
        if (is_null($user)) {
            return $this->teamInvoicePaymentSucceeded($payload);
        }
        info('kkkkk');
        if($user->billing_status=='Failed' || $user->billing_status=='Cancelled'){
        $failedcheck = FailedPayment::where(['user_id'=>$user->id,'invoice_id'=>$payload['data']['object']['id']])->first();
        info('handleInvoicePaymentSucceededFailed-'.json_encode($failedcheck));
        if($failedcheck!=null){
        $user->billing_status = 'Active'; 
        $status = \App\Status::where('name', 'payment_on_hold')->first();
        $user->status_id = $status->id;
        }
        }
        $user->save();
        $invoice = $user->findInvoice($payload['data']['object']['id']);
        $invoice = app(StripeLocalInvoiceRepository::class)->createForUser($user, $invoice);
        Log::info(json_encode($invoice));
        $line_items = collect($invoice['lines']);
        $overage_item = $line_items->where('description', 'Adilo Overage Charge')->first();
        $discount = 0;
        if($object['discount']!=null){
            $discount = (float)$object['discount']['coupon']['amount_off']/100;
        }
        info('kkkop--'.json_encode([
            'invoice' => $invoice->attributes,
            'user' => $user,
            'company' => Spark::$details,
            'plan' => $plan,
            'discount' => $discount,
            'overage_cost' =>  $overage_item ? $overage_item->amount / 100 : 0,
            'total' =>  $invoice->total,
            'base_url' => config('app.root_url'),
            'site_name' => config('app.site_url'),
            'title' => 'Your subscription has been renewed.'
        ]));
        Mail::send('/billing/invoice', [
            'invoice' => $invoice->attributes,
            'user' => $user,
            'company' => Spark::$details,
            'plan' => $plan,
            'discount' => $discount,
            'overage_cost' =>  $overage_item ? $overage_item->amount / 100 : 0,
            'total' =>  $invoice->total,
            'base_url' => config('app.root_url'),
            'site_name' => config('app.site_url'),
            'title' => 'Your subscription has been renewed.'
        ], function ($m) use ($user, $subjects, $invoice) {
            $m->to($user->email)->subject($subjects['invoice'] . ' ' . $invoice->receipt_id);
        });
    }
        return new Response('Webhook Handled', 200);
  
    }

    /**
     * Handle a successful invoice payment from a Stripe subscription.
     *
     * @param  array  $payload
     * @return Response
     */
    protected function teamInvoicePaymentSucceeded(array $payload)
    {
        $team = Spark::team()->where(
            'stripe_id', $payload['data']['object']['customer']
        )->first();

        if (is_null($team)) {
            return;
        }

        $invoice = $team->findInvoice($payload['data']['object']['id']);

        app(StripeLocalInvoiceRepository::class)->createForTeam($team, $invoice);

        $this->sendInvoiceNotification(
            $team, $invoice
        );
        info('teamInvoicePaymentSucceeded');

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return Response
     */
    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        parent::handleCustomerSubscriptionDeleted($payload);

        $user = $this->getUserByStripeId($payload['data']['object']['customer']);

        if (! $user) {
            return $this->teamSubscriptionDeleted($payload);
        }

        event(new SubscriptionCancelled(
            $this->getUserByStripeId($payload['data']['object']['customer']))
        );
        info('handleCustomerSubscriptionDeleted');

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return Response
     */
    protected function teamSubscriptionDeleted(array $payload)
    {
        $team = Spark::team()->where(
            'stripe_id', $payload['data']['object']['customer']
        )->first();

        if ($team) {
            $team->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->stripe_id === $payload['data']['object']['id'];
            })->each(function ($subscription) {
                $subscription->markAsCancelled();
            });
        } else {
            return new Response('Webhook Handled', 200);
        }

        event(new TeamSubscriptionCancelled($team));

        return new Response('Webhook Handled', 200);
    }
    public function handleInvoiceUpdated($payload)
    {
        info('handleInvoiceUpdated->'.json_encode($payload));
    }
    public function handleInvoiceCreated($payload)
    {
        try{
        $subscription_id = null;
        $subscription_stripeId = null;
        $plan_id = null;

        if ($payload['data']['object']['subscription']) {
            $subscription = \App\UserSubscriptions::where('stripe_id', $payload['data']['object']['subscription'])->first();
            if ($subscription) {
                $actualSub = \App\Subscription::where('stripe_id', $payload['data']['object']['subscription'])->first();
                $subscription_id = ($actualSub!=null)?$actualSub->id:$subscription->id;
                $subscription_stripeId = $subscription->stripe_id; 
                $plan_id = $subscription->stripe_plan;
            }
        }
        
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        // $user = $this->getUserByStripeId('cus_HcFevVJSpLzyWP');
        $invoice_data = $payload['data']['object'];
        info('handleInvoiceCreated->'.json_encode($user));
        info('handleInvoiceCreated->'.json_encode($invoice_data));
        $line_items = collect($invoice_data['lines']);
        // info('handleInvoiceCreated->'.json_encode($lines));
      
        $overage_item = $line_items->where('description', 'Adilo Overage Charge')->first();
        $invoice = Invoice::where('original_id', $payload['data']['object']['id'])->first();
        if($invoice==null){
            $invoice = $this->generateInvoice($user,$invoice_data,$subscription_id,$plan_id);
        }
        info('handleInvoiceCreated');
        
        return new Response('handleInvoiceCreated handled', 200);
        }catch (Exception $e) {
            info(json_encode($e->getMessage()));
            return new Response('handleInvoiceCreated handled', 200);
		}

      
    }
    function generateInvoice($user,$invoice_data,$subscription_id,$plan_id){

        $lines = $invoice_data['lines'];

        $overusage = $this->calculateOverUsage($lines['data'],$plan_id);
        $discount = 0;
            if($invoice_data['discount']!=null){
                $discount = (float)$invoice_data['discount']['coupon']['amount_off']/100;
            }
        info('-----p');
        info(json_encode([
            'original_id' => $invoice_data['id'],
            'subscription_id' => $subscription_id,
            'customer' => $invoice_data['customer'],
            'system_name' => 'Stripe',
            'paid' => $invoice_data['paid'],
            'status' => $invoice_data['status'],
            'description' => $invoice_data['description'],
            'discount' => $discount,
            'provider_id' => 'stripe',
            'total' => $invoice_data['total'] / 100,
            'tax' => $invoice_data['tax'] || 0,
            'billing_state' => $user ? $user->billing_state : null,
            'billing_country' => $user ? $user->billing_country : null,
            'billing_zip' => $user ? $user->billing_zip : null,
            'card_country' => $user ? $user->card_country : null,
            'created_at' => $invoice_data['created'],
            'team_id' => $user ? $user->currentTeam()->id : null,
            'overage_cost' => $overusage,
            // 'overage_cost' => $overage_item ? $overage_item->amount / 100 : 0,
            'user_id' => $user ? $user->id : null
        ]));
        return  Invoice::create([
            'original_id' => $invoice_data['id'],
            'subscription_id' => $subscription_id,
            'customer' => $invoice_data['customer'],
            'system_name' => 'Stripe',
            'paid' => $invoice_data['paid'],
            'status' => $invoice_data['status'],
            'description' => $invoice_data['description'],
            'provider_id' => 'stripe',
            'discount' => $discount,
            'total' => $invoice_data['total'] / 100,
            'tax' => $invoice_data['tax'] || 0,
            'billing_state' => $user ? $user->billing_state : null,
            'billing_country' => $user ? $user->billing_country : null,
            'billing_zip' => $user ? $user->billing_zip : null,
            'card_country' => $user ? $user->card_country : null,
            'created_at' => $invoice_data['created'],
            'team_id' => $user ? $user->currentTeam()->id : null,
            'overage_cost' => $overusage,
            // 'overage_cost' => $overage_item ? $overage_item->amount / 100 : 0,
            'user_id' => $user ? $user->id : null
        ]);
    } 
    public function handleInvoicePaymentFailed($payload) {
       
        $subjects = config('app.mail_subjects');
            
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if($user){
        // $user = $this->getUserByStripeId('cus_HcFevVJSpLzyWP');
        $object = $payload['data']['object'];
        $subscription_id = null;
        $subscription_stripeId = null;
        $plan_id = null;

        if ($payload['data']['object']['subscription']) {
            $subscription = \App\UserSubscriptions::where('stripe_id', $payload['data']['object']['subscription'])->first();
            if ($subscription) {
                $subscription_id = $subscription->id;//id of subscription table
                $subscription_stripeId = $subscription->stripe_id; 
                $plan_id = $subscription->stripe_plan;
            }
        }
        $user->billing_status = 'Failed';
        if($object['attempt_count']=='4'){
            $user->billing_status = 'Cancelled'; 
        } 
        $invoice_data = $payload['data']['object'];
        info('handleInvoicePaymentFailed=>'.$user->email);
        info('handleInvoicePaymentFailed=>'.json_encode( $payload['data']));
        info('handleInvoicePaymentFailed_attempt_count=>'.$object['attempt_count']);
        info('invoice_id=>'. $payload['data']['object']['id']);
        info(Invoice::where('original_id', $payload['data']['object']['id'])->first());
        $invoice = Invoice::where('original_id', $payload['data']['object']['id'])->first();
        if($invoice==null){
            $invoice = $this->generateInvoice($user,$invoice_data,$subscription_id,$plan_id);
        }
        info('userinvoice->'.$invoice);
        $invoice->status = $payload['data']['object']['status'];
        $invoice->paid = false; 
        $invoice->save();
       
        $user->save();
        info('userinfo->'.$user);
        info('billing_status->'.$user->billing_status);
        $subjects['subscription_failed'] = 'ACTION REQUIRED: Your subscription renewal failed';
        $lines = $object['lines'];
        // info('handleInvoiceCreated->'.json_encode($lines));
        $overusage = $this->calculateOverUsage($lines['data'],$plan_id);
        if($object['attempt_count']=='0'){
            info('handleInvoicePaymentFailed_attempt_counppppt=>'.$object['attempt_count']);
           // $subjects['subscription_failed'] = 'ACTION REQUIRED: Your account is about to be suspended';
            $expdate = Carbon::now()->addDays(7)->toDateString();
            $this->manageFailedPayment($invoice,$object,$user,$expdate,$overusage);
        }  if($object['attempt_count']=='1'){
            info('handleInvoicePaymentFailed_attempt_counppppt=>'.$object['attempt_count']);
           // $subjects['subscription_failed'] = 'ACTION REQUIRED: Your account is about to be suspended';
            $expdate = Carbon::now()->addDays(7)->toDateString();
            $this->manageFailedPayment($invoice,$object,$user,$expdate,$overusage);
        }
        if($object['attempt_count']=='2'){
            $expdate = Carbon::now()->addDays(6)->toDateString();
            $this->manageFailedPayment($invoice,$object,$user,$expdate,$overusage);

        }
        if($object['attempt_count']=='3'){
            $expdate = Carbon::now()->addDays(4)->toDateString();
            $this->manageFailedPayment($invoice,$object,$user,$expdate,$overusage);

        }
        if($object['attempt_count']=='4'){
            $expdate = Carbon::now()->toDateString();
            $subjects['subscription_failed'] = 'ACTION REQUIRED: Your account is about to be suspended';
            $this->manageFailedPayment($invoice,$object,$user,$expdate,$overusage);

        }
 
        Mail::send('/billing/subscription_failed', [
            'user' => $user,
            'invoice' => $invoice,
            'enddate' => $expdate,
            'company' => Spark::$details,
            'base_url' => config('app.root_url'),
            'site_name' => config('app.site_url'),
        ], function ($m) use ($user, $subjects, $invoice) {
            $m->to($user->email)->subject($subjects['subscription_failed']);
        });
       

        return new Response('handleInvoicePaymentFailed handled', 200);
        }else{
            return new Response('handleInvoicePaymentFailed user not found', 200);
        }
     
    }
    public function handleCustomerSubscriptionCreated($payload) {
        $object = $payload['data']['object'];
         info('handleCustomerSubscriptionCreated->'.json_encode($payload['data']['object']));
         $object = $payload['data']['object']; 
    try{
        $user = $this->getUserByStripeId($object['customer']);
        
        if ($user) {
            if($object['status']=='incomplete' && $user->is_discount_applied=='1'){
                $status = \App\Status::where('name', 'payment_on_hold')->first();
                $user->status_id = $status->id;
                $user->save();
                $this->mailChimpAddMember($user->email);
            }else if($object['status']=='canceled'){
                $user->billing_status = 'Cancelled';
                $user->save();
                }
            
            // info('handleCustomerSubscriptionUpdated-=-sucess');
            return new Response('handleCustomerSubscriptionCreated handled', 200);
        }
        
 
        info('handleCustomerSubscriptionCreated');

        return new Response('handleCustomerSubscriptionCreated error (User not found)'.$object['customer'], 404);
        }catch (Exception $e) {
            info(json_encode($e->getMessage()));
            return new Response('handleCustomerSubscriptionCreatedFailed handled', 200);
        }

    }

    public function handleCustomerSubscriptionUpdated($payload) {
        $object = $payload['data']['object'];
         info('SubscriptionUpdated->'.json_encode($payload['data']['object']));
    try{
        $user = $this->getUserByStripeId($object['customer']);
        
        if ($user) {
            if($object['status']=='active'){
            $status = \App\Status::where('name', 'active')->first();
            $user->billing_status = ucfirst($object['status']);
            $user->status_id = $status->id;
            }else if($object['status']=='past_due' || $object['status']=='unpaid'){
            // $status = \App\Status::where('name', 'payment_on_hold')->first();
            // $user->status_id = $status->id;
            }else if($object['status']=='canceled'){
            //$status = \App\Status::where('name', 'payment_on_hold')->first();
            $user->billing_status = 'Cancelled';
            }
            $user->currentPlan->trial_ends_at = null;
            $user->currentPlan->ends_at =  date('Y-m-d H:i:s',$object['current_period_end']);;
            $user->save();
            $user->currentPlan->save();
            if($user->status_id==5){
                $this->mailChimpAddMember($user->email);
            }
            info('handleCustomerSubscriptionUpdated-=-sucess');
            return new Response('handleCustomerSubscriptionUpdated handled', 200);
        }
        
 
        info('handleCustomerSubscriptionUpdated');

        return new Response('handleCustomerSubscriptionUpdated error (User not found)'.$object['customer'], 404);
        }catch (Exception $e) {
            info(json_encode($e->getMessage()));
            return new Response('handleInvoicePaymentFailed handled', 200);
        }
    }
    function calculateOverUsage($data,$plan_id){
        $overusage = 0;
        info('calculateOverUsage->'.json_encode($data));
        if(count($data)>0){
            foreach($data as $val){
                $val = (object)$val;
                $val->plan = (object)$val->plan;
                if($val->plan!=null && $val->plan->usage_type=='metered'){
                    info($val->plan->id.'---');
                    info('amountsss--'.(float)$val->amount);
                    info('amount--'.(float)$val->amount/100);

                    $overusage = (float)$overusage + (float)$val->amount/100;
                }
            }
        }
        $overusage = number_format($overusage, 2, '.', '');
        return $overusage;
    }
    function manageFailedPayment($invoice,$object,$user,$canceldate,$overusage){
        $failed =  FailedPayment::where('user_id',$user->id)->first();
        $plan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
        info('--Failed_Payment--'.json_encode($failed));
        if($failed!==null){
        $failed->user_id = $user->id;
        $failed->invoice_id = $invoice->id;
        $failed->stripe_id = $invoice->original_id;
        $failed->attempt = $object['attempt_count'];
        // $failed->cancel_date = $canceldate;
        $failed->save();
        }else{ 
            $discount = 0;
            if($object['discount']!=null){
                $discount = (float)$object['discount']['coupon']['amount_off']/100;
            }
             
            info(json_encode([
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,
                'stripe_id' => $invoice->original_id,
                'attempt' => $object['attempt_count'],
                'cancel_date' => $canceldate,
                'total' =>$object['amount_due']/100,
                'overage' => $overusage,
                'plan_cost' => $plan->price,
                'credit' => $discount,
                'stripe_plan' => $user->currentPlan->stripe_plan,
            ]));
        $res = FailedPayment::create([
            'user_id' => $user->id,
            'invoice_id' => $invoice->id,
            'stripe_id' => $invoice->original_id,
            'attempt' => $object['attempt_count'],
            'cancel_date' => $canceldate,
            'total' =>$object['amount_due']/100,
            'overage' => $overusage,
            'plan_cost' => $plan->price,
            'credit' => $discount,
            'stripe_plan' => $user->currentPlan->stripe_plan,
        ]);
        info('9990----'.json_encode($res));
          
        }

    }

    public function handlesubScriptionScheduleUpdated($payload)
        {
            info('handlesubScriptionScheduleUpdated->'.json_encode($payload));
            $object = $payload['data']['object'];
            $user = $this->getUserByStripeId($object['customer']);
            if ($user) {
                if($object['status']=='')
            $schedule_res = SubscriptionSchedule::where(['user_id'=>$user->id,'sub_schd_id'=>$object['id']])->first();
            $plan = Spark::teamPlans()->where('id', $schedule_res->upcoming_plan_id)->first();
            $this->updateSubscriptionItems($plan, $user, $object['subscription']); 
            $schedule_res = SubscriptionSchedule::where(['user_id'=>$user->id,'sub_schd_id'=>$object['id']])->update(['status'=>$object['status']]);
            // $this->mailChimpAddMember($user->email);
            }
        }
    public function handleSubscriptionScheduleCreated($payload)
        {
            info('handleSubscriptionScheduleCreated->'.json_encode($payload));
        }
    public function handleSubscriptionScheduleCompleted($payload)
        {
            info('handleSubscriptionScheduleCompleted->'.json_encode($payload));
        }
    public function handleSubscriptionScheduleReleased($payload)
    {
        info('handleSubscriptionScheduleReleased->'.json_encode($payload));
    }
    public function handleSubscriptionScheduleCanceled($payload)
    {
        info('handleSubscriptionScheduleCanceled->'.json_encode($payload));
    }
    /*
		*update subscription item
	*/
	function updateSubscriptionItems($plan, $user,$subscription_id) {
        $update_subscription = $this->getSubscription($subscription_id);
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
    
    function getSubscription($id){
		return \Stripe\Subscription::retrieve($id);
	}
    function mailChimpAddMember($email){

        $merge_fields = [];
        $first_name = '';
        $last_name = '';
        $tags = ['payment on hold'];
        info('payment on hold');
        $stripe = new StripeHelper();
        $stripe->mailChimpAddMember($email,json_encode($tags),$merge_fields);
         
    }
}
