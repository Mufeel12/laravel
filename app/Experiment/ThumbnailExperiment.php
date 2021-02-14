<?php

namespace App\Experiment;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ThumbnailExperiment extends Model
{
    protected $fillable = ['project_id','video_id', 'active', 'duration', 'action', 'type', 'title', 'end_date', 'created_at'];
    public $timestamps = true;

    public function clickCounts()
    {
        return $this->hasMany('App\Experiment\ThumbnailClickCount', 'experiment_id');
    }

    public static function boot()
    {
        self::deleting(function($model) {
            $model->clickCounts()->delete();
        });
    }
}
