<?php

namespace App;

use App\Support\Adapters\VimeoAdapter;
use App\Support\Adapters\YoutubeAdapter;
use Illuminate\Database\Eloquent\Model;

class VideoDescription extends Model
{
    protected $table = 'video_description';

    protected $fillable = ['video_id', 'description'];

    /**
     * Returns description if doesn not exist, creates new
     *
     * @param Video $video
     * @return mixed|string
     */
    public static function getDescription(Video $video)
    {
        $entry = self::where('video_id', $video->id)->first();

        if (!$entry) {
            $description = self::defaultDescription($video);
            if (strlen($description) > 0) {
                $entry = new self();
                $entry->description = $description;
                $entry->video_id = $video->id;
                $entry->save();
            } else {
                return '';
            }
        }

        return $entry->description;
    }

    /**
     * Returns description from youtube or vimeo
     *
     * @param Video $video
     * @return string
     */
    public static function defaultDescription(Video $video)
    {
        $description = '';

        if (!empty($video->path)) {

            if ($video->source == 'youtube') {
                $yt = new YoutubeAdapter();
                $videoId = $yt->getVideoIdFromUrl($video->path);
                if (!empty($videoId)) {
                    $vid = $yt->getVideoById($videoId);
                }
            } elseif ($video->source == 'vimeo') {
                $vimeo = new VimeoAdapter();
                $videoId = $vimeo->getVideoIdFromUrl($video->path);
                if (!empty($videoId)) {
                    $vid = $vimeo->getVideoById($videoId);
                }
            }

            if (isset($vid)) {
                // Set the description
                if (isset($vid->snippet->description))
                    $description = $vid->snippet->description;
                else if (isset($vid['description']))
                    $description = $vid['description'];
            }
        }

        return trim($description);
    }
}
