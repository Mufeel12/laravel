<?php

namespace App\Http\Controllers;

use App\Providers\SparkServiceProvider;

use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Laravel\Spark\Spark;
use Laravel\Spark\LocalInvoice as Invoice;
use Illuminate\Support\Str;


class InvoiceController extends Controller
{
    public function test($invoice_id, $user_id) {
        // TODO: FOR INVOICE AND ESTIMATE
        // $user = User::find($user_id)->first();

        // $invoice = Invoice::with('subscription')
        //     ->where('id', $invoice_id)
        //     ->where('user_id', $user_id)
        //     ->first();

        // $plan = Spark::teamPlans()->where('id', $invoice->subscription->stripe_plan)->first();

        // return view('billing.invoice', [
        //     'invoice' => $invoice,
        //     'user' => $user,
        //     'company' => Spark::$details,
        //     'plan' => $plan,
        //     'base_url' => config('app.root_url'),
        //     'site_name' => config('app.site_url'),
        //     'title' => 'Your subscription has been renewed.'
        // ]);

        // TODO: FOR FAILED SUBSCRIPTION
        // $user = User::find($user_id)->first();
        // $subscription = $user->currentPlan;
        // $invoice = Invoice::with('subscription')
        //     ->where('user_id', $user_id)
        //     ->orderBy('id', 'desc')
        //     ->first();

        // return view('billing.subscription_failed', [
        //     'user' => $user,
        //     'invoice' => $invoice,
        //     'company' => Spark::$details,
        //     'base_url' => config('app.root_url'),
        //     'site_name' => config('app.site_url'),
        // ]);

        // TODO: FOR SUSPENDED USERS
        // return view('billing.account_suspended', [
        //     'company' => Spark::$details,
        //     'base_url' => config('app.root_url'),
        // ]);
    }

    public static function getPdf($invoice_id, $user)
    {
        ini_set('max_execution_time', '300'); 
        $invoice = Invoice::with('subscription')
            ->where('id', $invoice_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $plan = Spark::teamPlans()->where('id', $invoice->subscription->stripe_plan)->first();

        $pdf = PDF::loadView('billing.invoice', [
            'invoice' => $invoice,
            'user' => $user,
            'company' => Spark::$details,
            'overage_cost'=>$invoice->overage_cost,
            'total'=>$invoice->total,
            'plan' => $plan,
            'base_url' => config('app.root_url'),
            'site_name' => config('app.site_url'),
            'title' => 'Your subscription has been renewed.'
        ]);

        return $pdf;
    }

    public function viewOrDownloadPdf(Request $request, $action, $invoice_id, $user_id = false)
    {
        $user = $user_id ? User::where('id', $user_id)->firstOrFail() : $request->user();
        $pdf = $this->getPdf($invoice_id, $user);

        if ($action === 'download') {
            return $pdf->download('invoice.pdf');
        }

        if ($action === 'view') {
            return $pdf->stream();
        }
    }

    public function sendEmailInvoice(Request $request, $invoice_id, $user_id = false)
    {
        $subjects = config('app.mail_subjects');

        $user = $user_id ? User::where('id', $user_id)->first() : $request->user();
        $invoice = Invoice::with('subscription')
            ->where('id', $invoice_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $plan = Spark::teamPlans()->where('id', $invoice->subscription->stripe_plan)->first();

        Mail::send('/billing/invoice', [
            'invoice' => $invoice,
            'user' => $user,
            'company' => Spark::$details,
            'plan' => $plan,
            'overage_cost'=>$invoice->overage_cost,
            'total'=>$invoice->total,
            'base_url' => config('app.root_url'),
            'site_name' => config('app.site_url'),
            'title' => 'Your invoice'
        ], function ($m) use ($user, $subjects, $invoice) {
            $m->to($user->email)->subject($subjects['invoice'] . ' ' . $invoice->receipt_id);
        });

        return response()->json([ 'success' => true ]);
    }
}
