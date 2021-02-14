<?php

namespace App\Experiment;

use Illuminate\Database\Eloquent\Model;

class VideoExperimentWinner extends Model
{
    protected $fillable = ['video_id', 'winner_id', 'video_experiment_id'];

    public function video()
    {
        return $this->belongsTo('App\Video');
    }
}
