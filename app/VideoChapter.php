<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoChapter extends Model
{
    protected $table = 'video_chapters';

    protected $fillable = [
        'id',
        'video_id',
        'title',
        'time',
        'index',
        'created_at',
        'updated_at',
    ];


}
