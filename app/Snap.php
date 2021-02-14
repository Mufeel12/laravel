<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Snap extends Model
{
    protected $fillable = [
        'snap_id', 'project_id', 'owner_id', 'team_id', 'title', 'description', 'views', 'filename', 'thumbnail', 'path', 'duration', '	wasabi_status'
    ];

    /**
     * Get the project detail.
     */
    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }
}
