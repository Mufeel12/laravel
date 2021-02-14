<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExperimentBrowserCookie extends Model
{
    protected $fillable = ['experiment_type', 'experiment_id', 'cookie', 'thumbnail_video_id'];
}
