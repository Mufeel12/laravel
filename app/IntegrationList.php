<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IntegrationList extends Model
{
    protected $table = 'integration_lists';

    protected $fillable = [
        'user_id', 'integration_id', 'lists'
    ];

    public function integration()
    {
        return $this->belongsTo('App\Integration', 'integration_id');
    }
}
