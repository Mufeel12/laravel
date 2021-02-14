<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Spark;

class PayKickSubscription extends Model
{
    protected $table = 'paykick_subscription';

    public function plan()
    {
        return $this->hasOne('App\PayKickPlan', 'id', 'plan_id');
    }

}

?>