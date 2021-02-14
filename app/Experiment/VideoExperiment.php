<?php

namespace App\Experiment;

use Illuminate\Database\Eloquent\Model;

class VideoExperiment extends Model
{
    protected $fillable = [
        'active',
        'title',
        'project_id',
        'video_id_a',
        'video_id_b',
        'goals',
        'duration',
        'action',
        'end_date',
        'created_at'
    ];

    protected $casts = [
        'goals' => 'array'
    ];

    public function experimentVideos()
    {
        return $this->hasMany('App\Experiment\ExperimentVideo');
    }

    public function winner()
    {
        return $this->hasOne('App\Experiment\VideoExperimentWinner');
    }
}
