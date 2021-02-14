<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoEventcode extends Model
{
    protected $table = 'video_eventcodes';

    protected $fillable = [
        'id',
        'basecode_id',
        'time',
        'code',
        'name',
        'created_at',
        'updated_at',
    ];

    public function baseCode(){
        return $this->hasOne(VideoBasecode::class, 'id', 'basecode_id');
    }
}
