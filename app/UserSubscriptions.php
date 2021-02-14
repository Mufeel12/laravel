<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Spark;
use Symfony\Component\HttpFoundation\Request;

class UserSubscriptions extends Model
{
    protected $table = 'users_subscription';
    protected $fill = ['subscription_id','user_id','stripe_plan'];
    public $timestamps = false;
}
