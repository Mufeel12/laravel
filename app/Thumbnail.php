<?php

namespace App;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Database\Eloquent\Model;
use Spoowy\VideoAntHelper\VideoAntHelper;

class Thumbnail extends Model
{
    /**
     * Returns path to output
     *
     * @param Video $video
     * @return string
     */
    protected static function getPreviewOutputPath(Video $video)
    {
        $outputPath = thumbnail_path($video->video_id) . '/preview';

        \Log::error($outputPath);

        // Create folder if it doesn't exist
        if (!\File::exists($outputPath)) {
            // Create main folder if nonexistent
            if (!\File::exists(thumbnail_path($video->video_id))) {
                \File::makeDirectory(thumbnail_path($video->video_id), 0775);
            }
            \File::makeDirectory($outputPath, 0775);
        }

        return $outputPath;
    }

    /**
     * Returns output image name
     *
     * @param Video $video
     * @param $width
     * @param $height
     * @return string
     */
    protected static function getPreviewOutputImageName(Video $video, $width, $height)
    {
        return $video->video_id . 'w_' . $width . '_h_' . $height . '_preview.jpg';
    }

    /**
     * Returns a full url from a file
     *
     * @param $url
     * @return string
     */
    protected static function getFullUrl($url)
    {
        if (starts_with($url, '/data/images')) {
            return 'https://app.motioncta.io' . $url;
        } elseif (starts_with($url, '/thumbnails')) {
            return 'https://sunshine.website' . $url;
        }
        return $url;
    }

    public static function generatePreviewThumbnailWithPlayButton(Video $video, $width = 200, $height = 150)
    {
        list($width, $height) = Image::adjustWidthAndHeight($width, $height);

        $thumbnail = $video->thumbnail;
        $playButton = public_path('img/video_play_button.png');

        try {
            \Bkwld\Croppa\Facade::reset($thumbnail);
            $croppedImage = \Bkwld\Croppa\Facade::url($thumbnail, $width, $height);
            $croppedImage = (env('APP_ENV') == 'local' ? config('env.ROOT_URL') : '') . $croppedImage;
        } catch (\Exception $e) {
            $croppedImage = \Bkwld\Croppa\Facade::url($thumbnail, $width, $height);
            $croppedImage = (env('APP_ENV') == 'local' ? config('env.ROOT_URL') : '') . $croppedImage;
        }

        $croppedImage = self::getFullUrl($croppedImage);

        if (!getimagesize($croppedImage)) {
            // Image does not exist

        }

        $png = imagecreatefrompng($playButton);
        $jpeg = imagecreatefromjpeg($croppedImage);

        // Set directory to thumbnails output
        $outputPath = self::getPreviewOutputPath($video);

        $thumbnailName = self::getPreviewOutputImageName($video, $width, $height);

        $destinationImage = $outputPath . $thumbnailName;

        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;

        list($sourceWidth, $sourceHeight) = getimagesize($croppedImage);
        list($playButtonWidth, $playButtonHeight) = getimagesize($playButton);
        $playButtonRatio = $playButtonWidth / $playButtonHeight;

        //$newPlayButtonHeight = $playButtonHeight; //($height / 2) * $playButtonRatio;
        //$newPlayButtonWidth = $playButtonWidth;//$playButtonWidth * $playButtonRatio;

        $newPlayButtonHeight = ($height / 2) * $playButtonRatio;
        $newPlayButtonWidth = $newPlayButtonHeight * $playButtonRatio;

        $playButtonLeftPosition = ($width / 2) - ($newPlayButtonWidth / 2);
        $playButtonTopPosition = ($height / 2) - ($newPlayButtonHeight / 2);

        // Before we create a new image, let's make sure there are less than 8 images there
        if (count(\File::files($outputPath)) > 10) {
            $files = \File::files($outputPath);
            for ($i = 0; $i <= count($files) - 8; $i++) {
                \File::delete($files[$i]);
            }
        }

        $newwidth = $width;
        $newheight = $height;
        $out = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($out, $jpeg, 0, 0, 0, 0, $newwidth, $newheight, $sourceWidth, $sourceHeight);
        imagecopyresampled($out, $png, $playButtonLeftPosition, $playButtonTopPosition, 0, 0, $newPlayButtonWidth, $newPlayButtonHeight, $playButtonWidth, $playButtonHeight);
        imagejpeg($out, $destinationImage, 100);
    }

    public static function deletePreviewThumbnail(Video $video)
    {
        // Delete all preview thumbnails when video thumbnail was changed
        \File::cleanDirectory(self::getPreviewOutputPath());
    }

    /**
     * Renders an image
     *
     * @param $destinationImage
     */
    protected static function renderPreviewImage($destinationImage)
    {
        if (\File::exists($destinationImage)) {
            // Set the content type header - in this case image/jpg
            header('Content-Type: image/jpeg');
            // Get image from file
            $img = imagecreatefromjpeg($destinationImage);
            // Output the image
            echo imagejpeg($img);
            die();
        }
    }

    /**
     * Renders and previews a thumbnail image
     *
     * @param Video $video
     * @param $width
     * @param $height
     * @return bool
     */
    public static function renderPreviewThumbnail(Video $video, $width, $height)
    {
        list($width, $height) = Image::adjustWidthAndHeight($width, $height);
        $outputPath = self::getPreviewOutputPath($video);
        $thumbnailName = self::getPreviewOutputImageName($video, $width, $height);

        $destinationImage = $outputPath . $thumbnailName;

        self::renderPreviewImage($destinationImage);

        // The image does not exist
        self::generatePreviewThumbnailWithPlayButton($video, $width, $height);

        self::renderPreviewImage($destinationImage);

        return false;
    }

    /**
     * Creates a default thumbnail
     *
     * @param Video $video
     * @return bool|string
     */
    public static function generateDefaultThumbnail(Video $video)
    {
        $thumbnail = '';

        try {

            // Amazon s3
            if ($video->source == 's3') {
                $thumbnail = self::generateAmazonThumbnail($video);
            }

            // Youtube
            if ($video->source == 'youtube') {
                $thumbnail = self::generateYoutubeThumbnail($video);
            }

            // Vimeo
            if ($video->source == 'vimeo') {
                $thumbnail = self::generateVimeoThumbnail($video);
            }

            // Fire success event
            VideoProcessingEvent::fire('thumbnail', $video, 'success');

        } catch (\Exception $e) {

            \Log::error($e->getMessage(), (array)$e);

            // Fire failure event
            VideoProcessingEvent::fire('thumbnail', $video, 'error');
        }

        return $thumbnail;
    }

    /**
     * Generates amazon thumbnail at half the video
     *
     * @param Video $video
     * @return bool
     */
    protected static function generateAmazonThumbnail(Video $video)
    {
        // Get half time
        $time = $video->duration / 2;
        $thumbnail = self::generate($video, $time, true);

        self::storeThumbnail($video, $thumbnail);

        return $thumbnail;
    }

    /**
     * Returns copied thumbnail
     *
     * todo: untested
     *
     * @param Video $video
     * @return mixed|string
     */
    protected static function generateYoutubeThumbnail(Video $video)
    {
        $youtubeVideoId = VideoImport::youtube_id_from_url($video->path);

        if ($youtubeVideoId) {
            // Get best thumbnail by youtube video id
            $thumbnail = VideoImport::getBestYoutubeThumbnail($youtubeVideoId);

            $thumbnail = VideoAntHelper::copyRemoteImage($thumbnail, $video->video_id); // Copy file

            // Save the thumbnail
            self::storeThumbnail($video, $thumbnail);

            return $thumbnail;
        }
    }

    /**
     * Copies vimeo thumbnail and returns url
     *
     * todo: untested
     *
     * @param Video $video
     * @return bool|string
     */
    protected static function generateVimeoThumbnail(Video $video)
    {
        // Get vimeo video id
        $vimeoVideoId = VideoImport::vimeo_id_from_url($video->path);
        if ($vimeoVideoId) {
            // Get best thumbnail by vimeo video id
            $thumbnail = VideoImport::getBestVimeoThumbnail($vimeoVideoId);
            $thumbnail = VideoAntHelper::copyRemoteImage($thumbnail, $video->video_id); // Copy file

            // Save the thumbnail
            self::storeThumbnail($video, $thumbnail);

            return $thumbnail;
        }
    }

    /**
     * Generates a thumbnail to a given time and stores it in db
     *
     * todo: untested
     *
     * @param Video $video
     * @param $time
     * @param bool $dontWait
     * @return bool
     * @throws \Exception
     */
    public static function generate(Video $video, $time, $dontWait = false)
    {
        // Get hq file path
        $videoFileHQ = $video->highest_quality_file;
        // TODO: sometimes this throws an error because fired too soon before transcoding is complete at all.

        #dd($videoFileHQ);
        $time = round($time);

        if ($videoFileHQ && isset($videoFileHQ->path)) {

            // Set directory to thumbnails output
            $output = thumbnail_path($video->video_id) . '/';

            // Create folder if it doesn't exist
            if (!\File::exists($output)) {
                \File::makeDirectory($output, 0775);
            }

            $thumbnailName = $video->video_id . '_' . round($time) . '.jpg';

            try {
                self::createFrame($videoFileHQ->path, $output . $thumbnailName, $time);
            } catch (\Exception $e) {
                \Log::alert($e->getMessage(), (array)$e);

                // Try one more time
                try {
                    self::createFrame($videoFileHQ->path, $output . $thumbnailName, $time);
                } catch (\Exception $e) {
                    \Log::alert($e->getMessage(), (array)$e);
                    throw new \Exception('A video quality file could not be accessed.');
                }
            }

            return thumbnail_path($video->video_id . '/' . $thumbnailName, true);
        }
        throw new \Exception('A video quality file could not be accessed.');

    }

    /**
     * Fires the ffmpeg, locally on this server
     *
     * @param $input
     * @param $output
     * @param $time
     */
    protected static function createFrame($input, $output, $time)
    {
        $ffmpeg = FFMpeg::create(array(
            'ffmpeg.binaries' => env('FFMPEG_PATH'),
            'ffprobe.binaries' => env('FFPROBE_PATH'),
            'timeout' => 3600, // The timeout for the underlying process
            'ffmpeg.threads' => 12,   // The number of threads that FFMpeg should use
        ));

        $ffmpegVideo = $ffmpeg->open($input);
        $ffmpegVideo
            ->frame(TimeCode::fromSeconds($time))
            ->save($output);
    }

    /**
     * Stores default thumbnail
     *
     * @param Video $video
     * @param $thumbnail
     */
    protected static function storeThumbnail(Video $video, $thumbnail)
    {
        $video->thumbnail = $thumbnail;
        $video->save();

        /*$videoDefaultThumbnail = new VideoDefaultThumbnail();
        $videoDefaultThumbnail->video_id = $video->id;
        $videoDefaultThumbnail->url = $thumbnail;
        $videoDefaultThumbnail->save();*/
    }
}
