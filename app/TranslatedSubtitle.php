<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranslatedSubtitle extends Model
{
    protected $table = 'translated_subtitles';
    use SoftDeletes;
    protected $fillable = [
        'id',
        'subtitle_id',
        'stored_name',
        'language',
        'url',
        'status',
        'lang_name',
        'created_at',
        'updated_at',
    ];

    public function subTitle(){
        return $this->hasOne(VideoSubtitle::class, 'id', 'subtitle_id');
    }

}
