<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelatedSnapVideo extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'snap_page_id',
        'video_id',
    ];

    public function video()
    {
        return $this->belongsTo('App\Video');
    }
}
