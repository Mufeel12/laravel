<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoQualityFile extends Model
{
    protected $table = 'video_quality_files';

    protected $fillable = ['*'];

    /**
     * Returns filename
     *
     * @param $path
     * @return string
     */
    public static function getQualityFilenameByPath($path)
    {
        return basename($path);
    }

    public static function duplicateQualityFile($oldVideoId, $newVideoId)
    {
        // Get video quality files
        $videoQualityFiles = self::where('video_id', $oldVideoId)->get();

        if (count($videoQualityFiles) > 0) {
            // Loop through quality files and create new ones
            foreach ($videoQualityFiles as $videoQualityFile) {
                $duplicateQualityFile = new self();

                $data = [
                    'video_id' => $newVideoId,
                    'format' => $videoQualityFile->format,
                    'path' => $videoQualityFile->path,
                    'status' => $videoQualityFile->status
                ];

                $duplicateQualityFile->save($data);
            }
        }
    }

    /**
     * Deletes all quality file entries and deletes quality files from S3 if not used anywhere else
     * Todo: make this work
     * @param $video
     */
    public static function deleteQualityFiles($video)
    {

        $qualityFiles = VideoQualityFile::where('video_id', $video->video_id)->get();
        /**
         * When do we delete a video file from s3? We delete a video file from s3
         * if it's path is nowhere else in use, except in the video quality file entries we want to delete
         */
        if (count($qualityFiles)) {
            foreach ($qualityFiles as $qualityFile) {

                $key = VideoQualityFile::getQualityFilenameByPath($qualityFile->path);

                // Instantiate the class
                $s3 = \App::make('aws')->get('s3');

                // Delete the object with BucketName and File Name as Parameter
                // Store the value in $deleteObject variable, which return data in Array format.
                $deleteObject = $s3->deleteObject([
                    // Bucket is required
                    'Bucket' => 'ctamonkey',
                    // Key is required
                    'Key' => $key
                ]);
                $qualityFile->delete();
            }
        }
    }

    /**
     * Deprecated: Update status of quality file by path for ElasticTranscoder
     *
     * @param ElasticTranscoderJob $entry
     * @return mixed
     */
    public static function updateStatus(ElasticTranscoderJob $entry)
    {
        // outputKey returns only filename, needs to become path to file
        $path = env('VIDEO_STORAGE') . '/production' . $entry->outputKey;

        $qualityFile = VideoQualityFile::where('path', $path)->first();
        if (count($qualityFile) > 0) {
            $qualityFile->status = $entry->outputStatus;
            return $qualityFile->save();
        }
    }

    /**
     * Get video
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function video()
    {
        return $this->hasOne('App\Video', 'video_id', 'video_id');
    }

    public static function serve($video)
    {
    }
}
