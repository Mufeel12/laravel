<?php

namespace App\Http\Controllers;

use App\Providers\SparkServiceProvider;

use Illuminate\Http\Request;
use Laravel\Spark\Spark;

class PlanController extends Controller
{
    public function all()
    {
        $p = Spark::teamPlans()->whereNotIn('id',['personal-paykickstart-static','marketer-paykickstart-static','commercial-paykickstart-static','elite-paykickstart-static'])->toArray();
         ksort($p);
         foreach($p as $val){
             $d[] = $val;
         }
        //return response()->json(Spark::allPlans());
        return response()->json($d);
    }

    public function getClientTokenForPayPal(Request $request)
    {
        $payPalEmail = $request->input('payPalEmail');
        $user = $request->user();
        return SparkServiceProvider::getClientTokenForPayPal($user, $payPalEmail);
    }

    public function getTrial(Request $request)
    {
        $pm = $request->input('paymentMethodToken');
        $subscription = $request->input('subscription');
        $user = $request->user();
        return SparkServiceProvider::getTrial($user, $pm, $subscription['plan_id']);
    }
}
