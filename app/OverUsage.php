<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Spark;

class OverUsage extends Model
{
    protected $table = 'user_over_usage';
    protected $fillable = ['user_id','service','over_usage','recorded_date','stripe_id','stripe_usage_id'];
     
}
