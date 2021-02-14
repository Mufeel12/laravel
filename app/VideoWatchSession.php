<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoWatchSession extends Model
{
    protected $fillable = ['watch_session_id', 'count', 'user_id'];

    public function stats()
    {
        return $this->hasMany('App\Statistic', 'watch_session_id', 'watch_session_id');
    }
}
