<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoFileOriginal extends Model
{
    protected $table = 'video_file_original';

    protected $fillable = ['video_id', 'path', 'status'];

    /**
     * Deletes original file from s3
     *
     * This must happen to save disk space, original file has been
     * converted successfully and the old, original file must be deleted
     *
     * @param Video $video
     * @internal param VideoQualityFile $videoQualityFile
     */
    public static function deleteOriginalFile(Video $video)
    {
        VideoProcessingEvent::fire('deleteOriginalFile', $video, 'success');
        return;
        // All Quality files have been transcoded successfully
        // Let's delete the original file

        // Get VideoQualityFile
        $originalFile = VideoFileOriginal::where('video_id', $video->video_id)->first();
        try {

            if (\Storage::exists($video->filename)) {
                // Delete the object with BucketName and File Name as Parameter
                \Storage::delete($video->filename);
            }

            // Let's update the storage entries
            $originalFile->status = 'Deleted';
            $originalFile->save();

            $videosWithOldPath = Video::where('path', $originalFile->path)
                ->get();

            if (count($videosWithOldPath)) {
                $videosWithOldPath->each(function($video) {
                    $mqFile = $video->middle_quality_file;
                    if (isset($mqFile->path)) {
                        $video->path = $mqFile->path;
                        $video->save();
                    }
                });
            }

            // Fire success event
            VideoProcessingEvent::fire('deleteOriginalFile', $video, 'success');

        } catch (\Exception $e) {
            \Log::error($e->getMessage(), (array)$e);
            VideoProcessingEvent::fire('path', $video, 'error');
        }

        try {
            $video->updateStorageInfo();
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), (array)$e);
        }
    }

    /**
     * Returns true if video is transcoded
     *
     * @return bool
     */
    public function isTranscoded()
    {
        return strtolower($this->status) == 'complete';
    }
}
