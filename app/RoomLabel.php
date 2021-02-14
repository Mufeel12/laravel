<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomLabel extends Model
{
    protected $table = 'room_labels';
    protected $fillable = ['user_id', 'label'];

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function rooms()
    {
    	return $this->hasMany('App\Room');
    }
}
