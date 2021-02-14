<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Madcoda\Youtube\Youtube;
use Vimeo\Vimeo;

class VideoImport extends Model
{
    public static function getSource($url) {
        if (VideoImport::isYoutubeUrl($url)) {
            return 'youtube';
        } elseif (VideoImport::isVimeoUrl($url)) {
            return 'vimeo';
        }
        return 's3';
    }
    /**
     * Verifies if url is a youtube url
     *
     * @param $url
     * @return bool
     */
    public static function isYoutubeUrl($url)
    {
        if (strpos($url, 'youtube.com') > 0 || strpos($url, 'youtu.be') > 0) {
            return true;
        }
        return false;
    }

    /**
     * Verifies vimeo url
     *
     * @param $url
     * @return bool
     */
    public static function isVimeoUrl($url)
    {
        $urls = parse_url($url);
        if ($urls) {
            if (isset($urls['host']) && $urls['host'] == 'vimeo.com') {
                return true;
            }
        }
        return false;
    }


    /**
     * Returns Youtube class instance
     *
     * @return Youtube
     */
    public static function getYoutubeInstance()
    {
        return new Youtube(['key' => env('YOUTUBE_API_KEY')]);
    }

    /**
     * Returns Vimeo class instance
     *
     * @return Vimeo
     */
    public function getVimeoInstance()
    {
        $consumerKey = env('VIMEO_KEY');
        $consumerSecret = env('VIMEO_SECRET');
        $vimeo = new Vimeo($consumerKey, $consumerSecret);
        $vimeo->setToken(env('VIMEO_ACCESS_TOKENA'));
        return $vimeo;
    }

    /**
     * Extracts video id from youtube video url
     *
     * @param $url
     * @return bool
     */
    public static function youtube_id_from_url($url)
    {
        $pattern =
            '%^# Match any youtube URL
            (?:https?://)?  # Optional scheme. Either http or https
            (?:www\.)?      # Optional www subdomain
            (?:             # Group host alternatives
              youtu\.be/    # Either youtu.be,
            | youtube\.com  # or youtube.com
              (?:           # Group path alternatives
                /embed/     # Either /embed/
              | /v/         # or /v/
              | /watch\?v=  # or /watch\?v=
              )             # End path alternatives.
            )               # End host alternatives.
            ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
            $%x';
        $result = preg_match($pattern, $url, $matches);
        if (false !== $result) {
            return (isset($matches[1]) ? $matches[1] : false);
        }
        return false;
    }

    /**
     * Extracts video id by vimeo video url
     *
     * @param $url
     * @return bool|string
     */
    public static function vimeo_id_from_url($url)
    {
        $urls = parse_url($url);
        if (isset($urls['host']) && $urls['host'] == 'vimeo.com') {
            $vimid = ltrim($urls['path'], '/');
            if ($exp = explode('/', $vimid)) {
                $vimid = (isset($exp[1]) ? $exp[1] : $vimid);
            }
            if (is_numeric($vimid)) {
                return $vimid;
            } else {
                $content = curl_get_contents($url);
                if ($content)
                    return self::vimeo_id_from_url($content);
            }
        }
        return false;
    }

    /**
     * Returns youtube thumbnail url without black bars
     *
     * @param $videoId
     * @return mixed
     */
    public static function getBestYoutubeThumbnail($videoId)
    {
        $yt = self::getYoutubeInstance();
        $res = $yt->getVideoInfo($videoId);
        // Get thumbnails
        $availableThumbnails = (array) $res->snippet->thumbnails;
        // Reverse array order
        $availableThumbnails = array_reverse($availableThumbnails);

        // Run through
        foreach ($availableThumbnails as $thumbnail) {
            if (ends_with($thumbnail->url, 'maxresdefault.jpg')
                || ends_with($thumbnail->url, 'hqdefault.jpg')
                || ends_with($thumbnail->url, 'mqdefault.jpg'))
                // Return thumbnail without black bars
                return $thumbnail->url;
        }
    }

    /**
     * Returns best vimeo thumbnail
     *
     * @param $videoId
     * @return bool
     */
    public static function getBestVimeoThumbnail($videoId)
    {
        $self = new self();
        $vm = $self->getVimeoInstance();
        $video = $vm->request('/videos/' . $videoId);
        if (isset($video['body']['pictures']['sizes'])) {
            return last($video['body']['pictures']['sizes'])['link'];
        }
        return false;
    }
}
