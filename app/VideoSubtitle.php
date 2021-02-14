<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoSubtitle extends Model
{
    protected $table = 'video_subtitles';
    use SoftDeletes;
    protected $fillable = [
        'id',
        'video_id',
        'stored_name',
        'language',
        'url',
        'status',
        'lang_name',
        'created_at',
        'updated_at',
        'sub_status',
        'job_id',
    ];

    public function translatedSubtitles()
    {
        return $this->hasMany(TranslatedSubtitle::class, 'subtitle_id', 'id');
    }

    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }

}
