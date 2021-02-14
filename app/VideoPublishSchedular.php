<?php

namespace App;

use App\Experiment\ThumbnailClickCount;
use App\Experiment\ThumbnailExperiment;
use App\Experiment\VideoExperiment;
use Aws\CommandPool;
use Aws\S3\S3Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Repositories\TeamRepository;
use Laravel\Spark\Spark;
use Spoowy\Commentable\Contracts\Commentable;
use Spoowy\Commentable\Traits\Commentable as CommentableTrait;
use Spoowy\VideoAntHelper\VideoAntHelper;

class VideoPublishSchedular extends Model
{
    protected $table = 'video_publish_schedule';
    protected $fillable = [
            'video_id',
            'user_id',
            'is_schedule',
            'stream_start_date',
            'stream_start_hour',
            'stream_start_min',
            'is_stream_start_text',
            'stream_start_text',
            'is_end_stream',
            'stream_end_date',
            'stream_end_hour',
            'stream_end_min',
            'is_stream_end_text',
            'stream_end_text',
            'is_action_button',
            'button_text',
            'button_link',
            
    ];

}