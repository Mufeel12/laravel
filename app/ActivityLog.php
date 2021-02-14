<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Request;
class ActivityLog extends Model
{
    protected $table = 'activity_log';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'activity_type', 'method', 'ip', 'subject', 'created_at'
    ];



}