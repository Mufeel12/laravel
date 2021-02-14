<?php

namespace App\Experiment;

use Illuminate\Database\Eloquent\Model;

class ThumbnailClickCount extends Model
{
    protected $fillable = ['type', 'clicks', 'url', 'experiment_id', 'overlay_text'];
}
