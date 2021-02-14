<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\InvoiceController;
use Braintree_Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserSettingsUpdateRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Mail;
use Laravel\Spark\Http\Controllers\Settings\API\TokenController;
use Laravel\Spark\LocalInvoice as Invoice;
use App\User;
use Laravel\Spark\Spark;
use App\Video;
use App\Card;
use Laravel\Spark\Token;

class BillingController extends Controller
{
    public function status($id = false, Request $request) {
        $user = $id ? User::where('id', $id)->first() : $request->user();
     
        return response()->json($user->getBillingStatus());
    }

    public function information($id = false, Request $request) {
      $user = $id ? User::where('id', $id)->first() : $request->user();
      $setting = $user->settings()->first();
      $invoices = Invoice::where('user_id', $user->id)
          ->with('subscription')
          ->orderBy('created_at', 'desc')
          ->take(4)
          ->get();

      return response()->json([
          'general' => [
              'name' => $user->name,
              'full_name' => $user->full_name,
              'email' => $user->email,
              'company' => $setting->company,
              'billing_country' => $user->billing_country ? $user->billing_country : $setting->country,
              'billing_city' => $user->billing_city ? $user->billing_city : $setting->city,
              'billing_zip' => $user->billing_zip ? $user->billing_zip : $setting->zip_code,
              'billing_address' => $user->billing_address ? $user->billing_address : $setting->street_address,
              'billing_address_line_2' => $user->billing_address_line_2,
              'billing_state' => $user->billing_state ? $user->billing_state : $setting->state,
              'paypal_email' => $user->paypal_email,
              'phone' => $user->phone,
          ],
          'preferences' => [
              'label_receipts' => $user->settings->label_receipts,
              'bill_estimates' => $user->settings->bill_estimates,
              'notify_email' => !$user->settings->notify_email ? $user->email : $user->settings->notify_email,
          ],
          'billing_history' => $invoices,
      ]);
    }

    public function invoices($id = false, Request $request) {
        $user = $id ? User::where('id', $id)->first() : $request->user();
        $setting = $user->settings()->first();
        $invoices = Invoice::with(['subscription'=>function($q) use($user){
            $q->where('user_id',$user->id);
        }])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($invoice) use ($user, $setting) {
                $invoice['plan'] = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
                $invoice['issuedTo'] = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'billing_country' => $user->billing_country ? $user->billing_country : $setting->country,
                    'billing_city' => $user->billing_city ? $user->billing_city : $setting->city,
                    'billing_zip' => $user->billing_zip ? $user->billing_zip : $setting->zip_code,
                    'billing_address' => $user->billing_address ? $user->billing_address : $setting->street_address,
                    'billing_address_line_2' => $user->billing_address_line_2,
                    'billing_state' => $user->billing_state ? $user->billing_state : $setting->state,
                    'phone' => $user->phone,
                ];
                $invoice['issuedBy'] = Spark::$details;

                return $invoice;
            });

        return response()->json($invoices);
    }

    public function payInvoice(Request $request, $invoice_id, $user_id = false) {
        $user = $user_id ? User::where('id', $user_id)->first() : $request->user();
        $invoice = Invoice::where('id', $invoice_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($invoice->paid) {
            return abort(422, 'The invoice paid already');
        }

        if ($user->payment_method === 'stripe') {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripe_invoice = \Stripe\Invoice::retrieve($invoice->original_id);
            return $stripe_invoice->pay();
        }

        if ($user->payment_method === 'paypal') {
            $gateway = new Braintree_Gateway(config('services.braintree'));
            return $gateway->subscription()->retryCharge($invoice->original_id);
        }

        return abort(500, 'Undefined user\'s payment method');
    }

    public function updateGeneral(UserUpdateRequest $request, $id = false) {
 
        $data = $request;
        $card = $data; 
        $user = $id ? User::where('id', $id)->first() : $request->user();
        if($request->has('payment_token')){
           $card =  $this->updateCustomerSource($user,$data);
        }elseif (isset($data['payment_method']) && $data['payment_method'] !== $user->payment_method) {
            $user->changePaymentMethod(
            $data,
            $request->input('payment_token')
          );
        }
        $user->save();
        
        return response()->json($card);
    }

    public function updatePreferences(UserSettingsUpdateRequest $request) {
        $data = $request->validated();
        $request->user()->settings->update($data);

        return response()->json($data);
    }

    public function estimate($id = false, Request $request) {
        $user = $id ? User::where('id', $id)->first() : $request->user();
        $plan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
        $setting = $user->settings()->first();
        $estimate = $user->getBillingEstimate();

        return response()->json([
            'estimate' => $estimate,
            'plan' => $plan,
            'issuedTo' => [
                'name' => $user->name,
                'email' => $user->email,
                'billing_country' => $user->billing_country ? $user->billing_country : $setting->country,
                'billing_city' => $user->billing_city ? $user->billing_city : $setting->city,
                'billing_zip' => $user->billing_zip ? $user->billing_zip : $setting->zip_code,
                'billing_address' => $user->billing_address ? $user->billing_address : $setting->street_address,
                'billing_address_line_2' => $user->billing_address_line_2,
                'billing_state' => $user->billing_state ? $user->billing_state : $setting->state,
                'phone' => $user->phone,
            ],
            'issuedBy' => Spark::$details,
        ]);
    }

    public function usage($id = false, Request $request) {
      $user = $id ? User::where('id', $id)->first() : $request->user();
      $videos = Video::where('owner', $user->id)->count();
      $plan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first();
      $bandwidthUsage = $user->summary->bandwidth_usage / 1024 / 1024 / 1024;

      return response()->json([
        'availableBandwidth' => $plan->attributes['bandwidth_limit'],
        'usedBandwidth' => $bandwidthUsage,
        'availableVideos' => $plan->attributes['videos_limit'] ? $plan->attributes['videos_limit'] : 'Unlimited',
        'usedVideos' => $videos,
        'overageBandwidth' => $user->getBandwidthOverage(),
        'usage'=> $user::getUserDetails($user)
      ]);
    }

    public function getInvoiceDataById(Request $request, $invoice_id, $user_id = false)
    {
        $user = $user_id ? User::where('id', $user_id)->first() : $request->user();
        $invoice = Invoice::with('subscription')
            ->where('id', $invoice_id)
            ->where('user_id', $user_id)
            ->first();

        if (!$invoice) {
            return abort(404, 'Invoice not found');
        }

        $issuedBy = Spark::$details;
        $setting = $user->settings()->first();

        return response()->json([
            'result' => $invoice,
            'receipt_id' => "BC-000000$invoice->id",
            'issued_to' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'company' => $setting->company,
                'email' => $user->email,
                'street_address' => $setting->street_address,
                'city' => $setting->city,
                'state' => $setting->state,
                'zip_code' => $setting->zip_code,
                'country' => $setting->country,
            ],
            'issued_by' => $issuedBy,
            'details' => [
                'paid' => date('Y-m-d H:i:s', strtotime($invoice->updated_at)),
            ],
            'billing' => [
                'monthly' => $invoice->total,
                'overage' => $invoice->overage_cost,
                'discounts' => '0.00',
            ],
            'paypal_email' => $user->paypal_email,
            'payment_method' => $user->payment_method,
        ]);
    }

    public function downloadPdf(Request $request, $invoice_id, $user_id = false)
    {
        $user = $user_id ? User::where('id', $user_id)->first() : $request->user();
        $pdf = InvoiceController::getPdf($invoice_id, $user->id);

        return $pdf->download('invoice.pdf');
    }

    public function sendEmailInvoice(Request $request, $invoice_id, $user_id = false)
    {
        $user = $user_id ? User::where('id', $user_id)->first() : $request->user();
        Mail::send('/billing/invoice', ['test' => 'test email'], function ($m) use ($user) {
            $m->to($user->email)->subject('test email');
        });
    }
    /**
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	function updateCustomerSource($user,$data){
        $token = $data['payment_token'];
         
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $customer = \Stripe\Customer::retrieve($user->stripe_id);
        $res2 = \Stripe\Token::retrieve($token);
        $card = Card::where(['user_id'=>$user->id,'fingerprint'=>$res2->card->fingerprint])->first();
            if($card==null){
                $source =  $customer->createSource(
                    $customer->id,
                    ['source' => $token]
                ); info('token->'.json_encode($source)); 

                $card = Card::create([
                    'user_id'=>$user->id,
                    'fingerprint'=>$res2->card->fingerprint,
                    'source_id' => $source->id,
                    'exp_year' => $data['exp_year'],
                    'exp_month' => $data['exp_month'],
                    'card_brand' => $data['cardType'],
                    'card_last_four' => $data['cardLast4']
                    ]
                );
            }
           return $card;  
            // $customer = \Stripe\Customer::update($user->stripe_id,['default_source'=>$card->source_id]);
            

        //\Stripe\Subscription::update($user->currentPlan->stripe_id ,['default_payment_method'=>$token]);
		  
    }
    
    function getInvoiceById(Request $request,$id){
        $user = $request->user();
        $res = Invoice::where(['user_id'=> $user->id,'id'=>$id])
        ->first();
        if($res!==null){
            $res->plan = Spark::teamPlans()->where('id', $user->currentPlan->stripe_plan)->first(); 
            $res->discount = (float)$res->discount;
        }
        return response()->json($res);
    }
    function getUserCards(Request $request){
        $user = $request->user();
        $res = Card::where('user_id',$user->id)->get();
        return response()->json($res);
    }
    function removeCard(Request $request,$id){
        $user = $request->user();
        $card = Card::where(['user_id'=>$user->id,'id'=>$id])->first();
        if($card){
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        // $customer = \Stripe\Customer::retrieve($user->stripe_id);
        $customer = \Stripe\Customer::deleteSource($user->stripe_id,$card->source_id);
        $card->delete();
        return response()->json(['result'=>'success']);
        }
      
    }
    function makeCardPrimary(Request $request,$id){
        $user = $request->user();
        $card = Card::where(['user_id'=>$user->id,'id'=>$id])->first();
        if($card){ 
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        // $customer = \Stripe\Customer::retrieve($user->stripe_id);
        $customer = \Stripe\Customer::update($user->stripe_id,['default_source'=>$card->source_id]);
        Card::where(['user_id'=>$user->id])->update(['default_card'=>'no']);
        $card->default_card = 'yes';
        $card->save();
        $user->exp_year=$card->exp_year;
        $user->exp_month=$card->exp_month;
        $user->card_brand=$card->card_brand;
        $user->card_last_four=$card->card_last_four;
        $user->save();
        return response()->json(['result'=>'success']);
        }
    }
}
