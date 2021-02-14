<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoResolution extends Model
{
    protected $table = 'video_resolutions';

    protected $fillable = [ 'video_id', 'name', 'file_path', 'file_size' ];

}
