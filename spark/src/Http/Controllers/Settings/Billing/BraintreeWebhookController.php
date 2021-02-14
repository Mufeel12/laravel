<?php

namespace Laravel\Spark\Http\Controllers\Settings\Billing;

use Braintree_Gateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Braintree\WebhookNotification;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Laravel\Spark\LocalInvoice as Invoice;
use Laravel\Spark\Spark;
use Laravel\Spark\TeamSubscription;
use Laravel\Spark\Events\Subscription\SubscriptionCancelled;
use Laravel\Spark\Contracts\Repositories\LocalInvoiceRepository;
use Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled as TeamSubscriptionCancelled;

class BraintreeWebhookController extends WebhookController
{
    use SendsInvoiceNotifications;

    public function handleWebhook(Request $request)
    {
        info('---called---');
        $gateway = new Braintree_Gateway(config('services.braintree'));
        $webhookNotification = $gateway->webhookNotification()->parse(
            $request['bt_signature'],
            $request['bt_payload']
        );
        $method = 'handle'.studly_case(str_replace('.', '_', $webhookNotification->kind));

        if (method_exists($this, $method)) {
            Log::info('method_exists($this, $method) - ' . $webhookNotification->kind);
            return $this->{$method}($webhookNotification);
        }

        return $this->missingMethod();
    }
    /**
     * Handle a successful invoice payment from a Braintree subscription.
     *
     * By default, this e-mails a copy of the invoice to the customer.
     *
     * @param  WebhookNotification  $webhook
     * @return Response
     */

    protected function teamSubscriptionChargedSuccessfully($webhook)
    {
        $subscription = TeamSubscription::where(
            'braintree_id', $webhook->subscription->id
        )->first();

        if (! $subscription || ! isset($webhook->subscription->transactions[0])) {
            return;
        }

        $invoice = $subscription->team->findInvoice(
            $webhook->subscription->transactions[0]->id
        );

        app(LocalInvoiceRepository::class)->createForTeam(
            $subscription->team, $invoice
        );

        $this->sendInvoiceNotification($subscription->team, $invoice);
    }

    /**
     * Handle a subscription cancellation notification from Braintree.
     *
     * @param  string  $subscriptionId
     * @return Response
     */
    protected function cancelSubscription($subscriptionId)
    {
        parent::cancelSubscription($subscriptionId);

        if (! $this->getSubscriptionById($subscriptionId)) {
            return $this->cancelTeamSubscription($subscriptionId);
        }

        if ($subscription = $this->getSubscriptionById($subscriptionId)) {
            event(new SubscriptionCancelled($subscription->user));
        }

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle a subscription cancellation notification from Braintree.
     *
     * @param  string  $subscriptionId
     * @return Response
     */
    protected function cancelTeamSubscription($subscriptionId)
    {
        $subscription = TeamSubscription::where(
            'braintree_id', $subscriptionId
        )->first();

        if ($subscription && ! $subscription->cancelled()) {
            $subscription->markAsCancelled();

            event(new TeamSubscriptionCancelled($subscription->team));
        }

        return new Response('Webhook Handled', 200);
    }

    static public function getUserBySubscriptionIdBraintree($subscriptionId)
    {
        $gateway = new Braintree_Gateway(config('services.braintree'));

        $pm = $gateway->subscription()->find($subscriptionId)->paymentMethodToken;
        return $gateway->customer()->search([
            \Braintree_CustomerSearch::paymentMethodToken()->is($pm),
        ])->firstItem();
    }

    static public function getSubscriptionPlanIdBySubscriptionId($subscriptionId)
    {
        $gateway = new Braintree_Gateway(config('services.braintree'));

        return $gateway->subscription()->find($subscriptionId)->planId;
    }

//    public function handleSubscriptionWentActive($payload)
//    {
//        $this->handleSubscriptionChargedSuccessfully($payload);
//    }

//    public function handleTrialEnded($payload)
//    {
//        $this->handleSubscriptionChargedSuccessfully($payload);
//    }

    public function handleSubscriptionChargedSuccessfully($payload)
    {
        Log::info(json_encode($payload));

        $userBraintree = BraintreeWebhookController::getUserBySubscriptionIdBraintree($payload->subscription->id);
        $user = \App\User::where('braintree_id', $userBraintree->id)->first();
        $planId = $user->currentPlan()->first()->id;

        $invoice = Invoice::create([
            'original_id' => $payload->subscription->transactions[0]->id,
            'subscription_id' => $planId,
            'customer' => $userBraintree->id,
            'system_name' => 'braintree',
            'paid' => $payload->subscription->transactions[0]->amount,
            'status' => $payload->subscription->transactions[0]->status,
            'description' => 'description',
            'provider_id' => 'braintree',
            'total' => $payload->subscription->transactions[0]->amount,
            'tax' => 0.00,
            'billing_state' => $user ? $user->billing_state : null,
            'billing_country' => $user ? $user->billing_country : null,
            'billing_zip' => $user ? $user->billing_zip : null,
            'card_country' => $user ? $user->card_country : null,
            'user_id' => $user->id,
            'team_id' => $user->currentTeam()->id,
        ]);
        $subjects = config('app.mail_subjects');

        $invoiceWithSub = Invoice::with('subscription')
            ->where('id', $invoice->id)
            ->firstOrFail();
        $plan = Spark::teamPlans()->where('id', $invoiceWithSub->subscription->stripe_plan)->first();
        Mail::send('/billing/invoice', [
            'invoice' => $invoiceWithSub,
            'user' => $user,
            'company' => Spark::$details,
            'plan' => $plan,
            'base_url' => config('app.root_url'),
            'site_name' => config('app.site_url'),
            'title' => 'Your subscription has been renewed.'
        ], function ($m) use ($user, $subjects, $invoiceWithSub) {
            $m->to($user->email)->subject($subjects['invoice'] . ' ' . $invoiceWithSub['receipt_id']);
        });

        return new Response('handleInvoiceCreated handled', 200);
    }
}
