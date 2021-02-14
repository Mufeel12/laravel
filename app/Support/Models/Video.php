<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 14.10.2015
 * Time: 20:35
 */

namespace App\Support\Models;

use Jenssegers\Model\Model;

/**
 * Class Video
 * @package App\Support\Adapters\Models
 */
class Video extends Model
{
    protected $fillable = [
        'type',
        'source',
        'path',
        'title',
        'video_id',
        'date',
        'human_date',
        'description',
        'channel_url',
        'channel_title',
        'is_imported',
        'duration',
        'duration_formatted',
        'thumbnail',
        'views'
    ];
}