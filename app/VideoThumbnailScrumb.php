<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class VideoThumbnailScrumb extends Model
{
    /**
     * Sends request to generate thumbnail scrumb
     *
     * @param Video $video
     */
    public static function generate(Video $video)
    {
        // Supply with lowest quality file, only for files from s3
        if (!$video->imported) {
            $video->path = $video->lowest_quality_file;
        }


        \Log::error($video->toJson());

        try {
            // Sent a post request to video ant
            $client = new Client();
            $res = $client->get(env('VIDEOANT_SCRUMB_URL') . '?' . http_build_query($video->toArray()));
            VideoProcessingEvent::fire('scrumb', $video, 'success');
        } catch (\Exception $e) {
            // The thumbnail scrumb creation has failed. What next?
            \Log::error($e->getMessage(), (array)$e);
            VideoProcessingEvent::fire('scrumb', $video, 'error');
        }
    }
}
