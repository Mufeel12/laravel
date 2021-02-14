<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TutorialVideo extends Model
{
    protected $fillable = ['type', 'video_id'];

    public function video()
    {
        return $this->belongsTo('App\Video');
    }
}
