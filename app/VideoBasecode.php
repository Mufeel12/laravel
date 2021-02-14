<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoBasecode extends Model
{
    protected $table = 'video_basecodes';

    protected $fillable = [
        'id',
        'video_id',
        'code',
        'name',
        'created_at',
        'updated_at',
    ];
    public function eventCode(){
        return $this->hasMany(VideoEventcode::class, 'basecode_id', 'id');
    }
    public function video(){
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }

}
