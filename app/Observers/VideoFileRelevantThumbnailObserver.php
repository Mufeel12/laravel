<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 16.07.2015
 * Time: 20:19
 */

namespace App\Observers;
use App\Video;
use App\VideoRelevantThumbnail;


/**
 * Class VideoFileRelevantThumbnailObserver
 *
 * Observer for VideoFileRelevantThumbnail model
 * @package app\Observers
 */
class VideoFileRelevantThumbnailObserver
{
    /**
     * Model created event
     *
     * @param VideoRelevantThumbnail $video
     */
    public function created(VideoRelevantThumbnail $thumbnail)
    {
        // Sort of garbage collector of old thumbnails so it doesn't get too polluted
        $video = Video::where('video_id', $thumbnail->key)->first();
        if ($video && $video->exists()) {
            $thumbnailsToDelete = VideoRelevantThumbnail::where('key', $thumbnail->key)
                ->where('id', '!=', $thumbnail->id)
                ->where('url', '!=', $video->thumbnail)
                ->get();

            // Delete all older thumbnails
            if (count($thumbnailsToDelete) > 0) {
                $files = [];
                foreach ($thumbnailsToDelete as $thumbnail) {
                    // get filename of thumbnail from url
                    $filename = basename($thumbnail->url);
                    $filePath = thumbnail_path($thumbnail->key) . '/' . $filename;
                    $files[] = $filePath;
                    $thumbnail->delete();
                }
                \File::delete($files);
            }
        }
    }

    /**
     * Model updated event
     *
     * @param VideoRelevantThumbnail $video
     */
    public function updated(VideoRelevantThumbnail $thumbnail)
    {

    }

    /**
     * Model deleted event
     *
     * @param VideoRelevantThumbnail $video
     */
    public function deleted(VideoRelevantThumbnail $thumbnail)
    {

    }
}