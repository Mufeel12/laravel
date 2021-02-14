<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Spark;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    public function subscription_items() {
    return $this->hasMany('App\SubscriptionItem', 'subscription_id', 'id');
    }
}
