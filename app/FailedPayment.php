<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Spark;
use Laravel\Spark\LocalInvoice as Invoice;

class FailedPayment extends Model
{
    protected $table = 'failed_payment_attempts';
    protected $fillable = ['user_id','invoice_id','stripe_id','attempt','cancel_date','total','overage','stripe_plan','plan_cost','credit'];
     
    public function failed_payment()
    {
        return $this->belongsTo('App\User');
    }     
    public function invoice()
    {
        return $this->belongsTo('Laravel\Spark\LocalInvoice','invoice_id');
    }
}