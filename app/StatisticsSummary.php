<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StatisticsSummary extends Model
{
    protected $table = 'statistics_summary';

    protected $fillable = [
        'id',
        'total_actions_taken',
        'created_at',
        'updated_at',
        'video_total_watch_time',
        'event_offset_time',
        'video_views',
        'video_skipped_aheads',
        'skipped',
        'clicks',
        'email_captures'
    ];

    /**
     * Video scope
     *
     * @param Builder $query
     * @param $id
     * @return $this
     */
    public function scopeVideo(Builder $query, $id)
    {
        return $query->where('video_id', $id);
    }
}
