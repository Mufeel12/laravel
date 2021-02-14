<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockedEmail extends Model
{
    protected $table = 'blocked_emails';
    protected $fillable = ['email'];
}
