<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 16.07.2015
 * Time: 20:19
 */

namespace App\Observers;
use App\VideoProcessingEvent;
use App\VideoQualityFile;

/**
 * Class VideoQualityFileObserver
 *
 * Observer for VideoQualityFile model
 * @package app\Observers
 */
class VideoQualityFileObserver
{
    /**
     * Model created event
     *
     * @param VideoQualityFile $videoQualityFile
     * @internal param Video $video
     */
    public function created(VideoQualityFile $videoQualityFile)
    {

    }

    /**
     * Model updated event
     *
     * @param VideoQualityFile $videoQualityFile
     */
    public function updated(VideoQualityFile $videoQualityFile)
    {
        $unfinishedQualityFiles = VideoQualityFile::where('video_id', $videoQualityFile->video_id)
            ->where('status', '!=', 'Complete')
            ->get();

        if (count($unfinishedQualityFiles) <= 0) {

            $video = $videoQualityFile->video;

            // Set the new path to the trnascoded video object, away from original file name
            $mqFile = $video->middle_quality_file;
            if (isset($mqFile->path)) {
                $video->path = $mqFile->path;
                $video->save();
            }

            // Fire event
            VideoProcessingEvent::fire('transcode', $video, 'success');
        }
    }

    /**
     * Model deleted event
     *
     * @param VideoQualityFile $videoQualityFile
     */
    public function deleted(VideoQualityFile $videoQualityFile)
    {
    }
}