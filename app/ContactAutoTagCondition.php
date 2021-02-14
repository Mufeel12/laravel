<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactAutoTagCondition extends Model
{
    protected $table = 'contact_auto_tag_conditions';

    protected $fillable = ['auto_tag_id', 'watched', 'video_type', 'project_id', 'specific_videos', 'timeline_type', 'start_date', 'end_date', 'combination'];

    protected $hidden = ['auto_tag', 'created_at', 'updated_at'];

    public function auto_tag()
    {
        return $this->belongsTo('App\ContactAutoTag', 'auto_tag_id');
    }
}
