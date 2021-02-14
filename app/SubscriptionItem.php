<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Spark;

class SubscriptionItem extends Model
{
    protected $table = 'subscription_items';
    protected $fill = ['subscription_id','stripe_id','stripe_plan'];
    public function subscription() {
        return $this->belongsTo('\App\Subscription', 'subscription_id');
    }
}
