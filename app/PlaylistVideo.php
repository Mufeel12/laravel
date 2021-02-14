<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlaylistVideo extends Model
{
    protected $table = 'playlist_videos';

    protected $fillable = [
        'playlist',
        'video'
    ];
}
