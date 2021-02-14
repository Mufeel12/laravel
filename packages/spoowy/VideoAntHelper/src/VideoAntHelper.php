<?php

namespace Spoowy\VideoAntHelper;

use App\VideoRelevantThumbnail as VideoFileRelevantThumbnail;
use App\VideoThumbnailScrumb;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class VideoAntHelper
{
    /**
     * Returns array with file information
     * @param  string $input file input
     * @param  string $type output format
     * @return array, json, xml, csv
     */
    public static function getMediaInfo($input, $type = null)
    {
        // Just making sure everything goes smooth
        if (substr($input, 0, 2) == '-i') {
            $input = substr($input, 3);
        }

        switch ($type) {
            case 'json':
                $command = self::getProbePath() . ' -v quiet -print_format json -show_format -show_streams -pretty -i ' . $input . ' 2>&1';
                $output = shell_exec($command);
                $output = json_decode($output, true);
                break;

            case 'xml':
                $command = self::getProbePath() . ' -v quiet -print_format xml -show_format -show_streams -pretty -i ' . $input . ' 2>&1';
                $output = shell_exec($command);
                break;

            case 'csv':
                $command = self::getProbePath() . ' -v quiet -print_format csv -show_format -show_streams -pretty -i ' . $input . ' 2>&1';
                $output = shell_exec($command);
                break;

            default:
                $command = self::getProbePath() . ' -v quiet -print_format json -show_format -show_streams -pretty -i ' . $input . ' 2>&1';
                $output = shell_exec($command);
                $output = json_decode($output, true);
                break;
        }

        return $output;
    }

    /**
     * Returns full path of ffprobe
     * @return string
     */
    protected static function getProbePath()
    {
        return env('FFPROBE_PATH');
    }

    /**
     * Retrieve video duration
     * @param string $input video input
     * @return string duration
     */
    public static function getDuration($input)
    {
        $command = self::getConverterPath() . ' -i "' . $input . '" 2>&1 | grep "Duration"| cut -d \' \' -f 4 | sed s/,// | sed \'s@\..*@@g\' | awk \'{ split($1, A, ":"); split(A[3], B, "."); print 3600*A[1] + 60*A[2] + B[1] }\'';
        $output = shell_exec($command);
        $output = intval(strval(trim($output)));
        return $output;
    }

    /**
     * Returns full path of ffmpeg
     * @return string
     */
    protected static function getConverterPath()
    {
        return env('FFMPEG_PATH');
    }

    /**
     * Retrieves video thumbnails
     * @param $inputKey
     * @param $outputKey
     * @param $outputPrefix
     * @param int $outputCount
     * @param int $inputOffsetSeconds
     * @param string $outputPostfix
     * @param string $outputFormat
     * @return bool
     */
    public static function fire($inputKey, $outputKey, $outputPrefix = '', $outputCount = 100, $inputOffsetSeconds = 1, $outputPostfix = '_%d', $outputFormat = 'jpg', $dontWait = true)
    {
        // Round offset
        $inputOffsetSeconds = round($inputOffsetSeconds);
        $outputCount = round($outputCount);

        // Return false if user requests 0 frames or round function fails
        if ($outputCount < 1) {
            return false;
        }

        $videoAntUrl = self::getVideoAntUrl();
        $params = [
            'inputKey' => $inputKey,
            'inputOffset' => $inputOffsetSeconds,
            'outputKey' => $outputKey,
            'outputPrefix' => $outputPrefix,
            'outputPostfix' => $outputPostfix,
            'outputCount' => round($outputCount),
            'outputFormat' => $outputFormat,
            '_token' => 'CoolestTokenEverCreatedByErwin'
        ];

        // If no thumbnail name specified, queue without urgency
        if ($outputPostfix == '_%d') {
            $params['state'] = 'pending';
        } else {
            $params['state'] = 'urgent';
        }

        // Send a request to VideoAnt server
        if ($dontWait)
            post_without_wait($videoAntUrl, $params);
        else {
            $client = new Client();
            $response = $client->request('POST', $videoAntUrl, [
                'form_params' => $params
            ]);
        }

        $videoAntStorageUrl = self::getVideoAntStorageUrl();

        // Returns the thumbnail url
        return $videoAntStorageUrl . '/' . $outputKey . '/' . $outputPrefix . $outputKey . $outputPostfix . '.' . $outputFormat;
    }

    /**
     * Returns VideoAnt main action url
     * @return string
     */
    protected static function getVideoAntUrl()
    {
        return env('VIDEOANT_URL') . '/let/the/sun/shine/on/this/video';
    }

    /**
     * Returns /thumbnails storage url
     * @return string
     */
    public static function getVideoAntStorageUrl()
    {
        return env('VIDEOANT_URL') . '/thumbnails';
    }

    /**
     * Deprecated
     *
     * Returns array of relevant thumbnails
     * @param $key
     * @param int $num
     * @return array
     */
    public static function getRelevantThumbnails($key, $num = 3)
    {
        $return = [];

        // Add first default image
        // todo: return youtube default thumbnail as first image!
        $return[] = self::getVideoAntStorageUrl() . '/' . $key . '/' . $key . '_0.jpg';

        $num = $num - 1;

        // Try getting thumbnails from DB storage first
        $thumbnails = VideoFileRelevantThumbnail::where('key', $key)
            ->limit($num)
            ->remember(100)
            ->get();

        // Get array of thumbnail urls
        if (count($thumbnails) == $num) {
            foreach ($thumbnails as $thumbnail) {
                $return[] = $thumbnail->url;
            }
            return $return;
        }

        // Get thumbnails from VideoAnt and set in database
        $thumbs = curl_get_contents(self::getVideoAntRelevantThumbnailsUrl() . '?key=' . $key . '&num=' . $num);
        $thumbs = json_decode($thumbs);

        if (count($thumbs)) {
            foreach ($thumbs as $thumbnail) {
                // Create new entry in DB for each thumbnail
                $videoFileRelevantThumbnail = new VideoFileRelevantThumbnail();
                $videoFileRelevantThumbnail->key = $key;
                $videoFileRelevantThumbnail->url = $thumbnail;
                $videoFileRelevantThumbnail->save();

                $return[] = $thumbnail;
            }
        }
        return $return;
    }

    /**
     * Returns VideoAnt getting relevant thumbnails action url
     * @return string
     */
    protected static function getVideoAntRelevantThumbnailsUrl()
    {
        return env('VIDEOANT_URL') . '/thumbs';
    }

    /**
     * Tells VideoAnt to copy a remote image to thumbnails destination
     * @param $imageUrl
     * @param $outputKey
     * @return string
     */
    public static function copyRemoteImage($imageUrl, $outputKey)
    {
        $url = self::getVideoAntUrl();

        $params = [
            'type' => 'copyImage', // Tell the controller to only copy this image from url
            'path' => $imageUrl,
            'key' => $outputKey,
            '_token' => 'CoolestTokenEverCreatedByErwin'
        ];

        // Request to VideoAnt
        post_without_wait($url, $params);

        // TODO: Respond not as path but url !important!!!
        return self::getVideoAntStorageUrl() . '/' . $outputKey . '/' . $outputKey . '_0.jpg';
    }

    /**
     * Returns URL of VideoAntScrumb for outputKey
     * @param $outputKey
     * @param string $videoPath
     * @return string
     */
    public static function getVideoAntScrumb($outputKey, $videoPath = '')
    {
        // 3. No entry in database? Get via the url.
        $scrumb = env('VIDEOANT_URL') . '/thumbnails/' . $outputKey . '/scrumb.jpg';

        // 1. Check if thumbnail scrumb does exist for this video path
        if ($videoPath) {
            // 2. Get from database
            $dbScrumb = VideoThumbnailScrumb::where('path', $videoPath)->first();
            if (count($dbScrumb) > 0) {
                $scrumb = $dbScrumb->thumbnail_scrumb;
            } else {
                // Store in DB for further use
                $dbScrumb = new VideoThumbnailScrumb();
                $dbScrumb->path = $videoPath;
                $dbScrumb->thumbnail_scrumb = $scrumb;
                $dbScrumb->save();
            }
        }
        return $scrumb;
    }

    public static function getDurationByVideoInformation($videoInfo) {
        // Get seconds / duration of video
        $duration['duration'] = 0;
        $duration['duration_formatted'] = '0';

        if (isset($videoInfo['format']['duration'])) {
            $duration['duration'] = round(time_to_seconds($videoInfo['format']['duration']));
            $duration['duration_formatted'] = format_duration($duration['duration']);
        } elseif (isset($videoInfo->format->duration)) {
            $duration['duration'] = round(time_to_seconds($videoInfo->format->duration));
            $duration['duration_formatted'] = format_duration($duration['duration']);
        }

        return $duration;
    }
}
