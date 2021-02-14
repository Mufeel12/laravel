<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';
    protected $fillable = [
    	'title',
    	'description',
    	'main_video',
    	'presenter_video',
    	'audio_source',
    	'room_label_id',
        'room_id',
        'views',
        'presenter_image'
    ];

    public function roomLabel()
    {
    	return $this->belongsTo('App\RoomLabel');
    }
}
