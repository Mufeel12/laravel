<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComplianceRecords extends Model
{
    protected $table = 'compliance_records';

    protected $fillable = [
        'user_id',
        'issue_id',
        'resolution',
        'ends_at',
        'email_text',
    ];

    public function issue()
    {
    	return $this->belongsTo('App\Issue');
    }
}
