<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 16.07.2015
 * Time: 20:19
 */

namespace App\Observers;

use App\Comments;
//use App\CommentsRead;
use App\Project;
use App\Statistic;
use App\StatisticsEngagementReplay;
use App\StatisticsEngagementSkipped;
use App\StatisticsEngagementView;
use App\StatisticsSummary;
use App\Video;
use App\VideoFileStorage;
use App\VideoPlayerOption;
use App\VideoQualityFile;

/**
 * Class VideoObserver
 *
 * Observer for Video model
 * @package app\Observers
 */
class VideoObserver
{
    /**
     * Model created event
     *
     * @param Video $video
     */
    public function created(Video $video)
    {
        // Touch the project
        // $project = Project::find($video->project);
        // Update video count (deprecated)
        // $project->increment('video_count');
    }

    /**
     * Model updated event
     *
     * @param Video $video
     */
    public function updated(Video $video)
    {
        Project::find($video->project)->touch();
    }

    /**
     * Model deleted event
     *
     * @param Video $video
     */
    public function deleted(Video $video)
    {
        @Project::find($video->project)->touch();
        @Comments::where('commentable_id', $video->id)->delete();
//        @CommentsRead::where('video_id', $video->id)->delete();
        @Statistic::where('video_id', $video->id)->delete();
        @StatisticsSummary::where('video_id', $video->id)->delete();
        @VideoPlayerOption::whereVideoId($video->id)->delete();
    }
}
