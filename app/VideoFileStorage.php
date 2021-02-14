<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class VideoFileStorage extends Model
{
    protected $table = 'video_file_storage';

    protected $fillable = ['video_id', 'size', 'quality_file_sizes', 'total_size'];

    /**
     * Returns free storage space in bytes
     *
     * @param $userId
     * @param bool $comprehensive
     * @return int
     */
    public static function getFreeSpace($userId, $comprehensive = false)
    {
        $storage = self::whereUserId($userId)->get();
        $totalStorageUsed = 0;

        $storage->each(function ($index, $key) use (&$totalStorageUsed) {
            $totalStorageUsed += $index->total_size;
        });

        // Get user settings storage
        $userSettings = UserSettings::whereUserId($userId)->first();
        if (!isset($userSettings->storage_space) || empty($userSettings->storage_space)) {
            $userSettings->storage_space = env('VIDEO_STORAGE_DEFAULT');
            $userSettings->save();
        }

        $freeSpace = intval($userSettings->storage_space) - $totalStorageUsed;

        if ($comprehensive === true) {
            // We want a comprehensive return
            $freeSpace = [
                'free_space' => [
                    'bytes' => $freeSpace,
                    'bytes_formatted' => format_bytes($totalStorageUsed)
                ],
                'total_space' => [
                    'bytes' => $userSettings->storage_space,
                    'bytes_formatted' => format_bytes($userSettings->storage_space)
                ],
                'used' => [
                    'bytes' => $totalStorageUsed,
                    'bytes_formatted' => format_bytes($totalStorageUsed),
                    'percentage' => ($totalStorageUsed / $userSettings->storage_space) * 100,
                    'percentage_formatted' => round(($totalStorageUsed / $userSettings->storage_space) * 100, 2) . '%'
                ]
            ];
        }

        return $freeSpace;
    }

    public static function getStorageInfoForVideo($video)
    {
        // Get all files for that video
        $qualityFiles = VideoQualityFile::where('video_id', $video->video_id)->get();

        $qualityFileSizes = [];
        $totalFileSize = 0;
        $standardVideoFileSize = 0;
        foreach ($qualityFiles as $key => $value) {
            $filename = VideoQualityFile::getQualityFilenameByPath($qualityFiles[$key]->path);
            $fileSize = self::getSizeOfFile($filename);
            $qualityFileSizes[] = [
                'filename' => $filename,
                'size' => $fileSize
            ];
            // Set standard video file size
            if ($video->video_id . '.mp4' == $filename)
                $standardVideoFileSize = $fileSize;

            $totalFileSize += $fileSize;
        }

        return [
            'size' => $standardVideoFileSize,
            'totalSize' => $totalFileSize,
            'qualityFiles' => $qualityFileSizes
        ];
    }

    /**
     * Returns size of file from s3
     *
     * @param $filename
     * @return bool
     */
    public static function getSizeOfFile($filename)
    {
        return \Storage::size('production/'. $filename);
        /*$s3 = App::make('AWS')->get('s3');
        $result = $s3->getObject([
            'Bucket' => env('AWS_BUCKET'),
            'Key' =>
        ]);
        return (isset($result['ContentLength']) ? $result['ContentLength'] : false);*/
    }

    /**
     * Updates storage info for video
     *
     * @param Video $video
     * @return mixed
     */
    public static function updateStorageInfoForVideo(Video $video)
    {
        if (count($video) > 0) {
            $storageInfo = self::getStorageInfoForVideo($video);

            $videoStorage = VideoFileStorage::whereVideoId($video->video_id)->first();
            if (count($videoStorage) <= 0) {
                $videoStorage = new VideoFileStorage();
                $videoStorage->video_id = $video->video_id;
                $videoStorage->user_id = $video->owner;
            }
            $videoStorage->size = $storageInfo['size'];
            $videoStorage->quality_file_sizes = json_encode($storageInfo['qualityFiles']);
            $videoStorage->total_size = $storageInfo['totalSize'];
            $videoStorage->save();

            return $storageInfo['totalSize'];
        }
    }
}
