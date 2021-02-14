<?php

namespace App;

use Carbon\Carbon;
use App\Experiment\ThumbnailClickCount;
use App\Experiment\ThumbnailExperiment;
use App\Experiment\VideoExperiment;
use Aws\CommandPool;
use Aws\S3\S3Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Spark\Repositories\TeamRepository;
use Laravel\Spark\Spark;
use Spoowy\Commentable\Contracts\Commentable;
use Spoowy\Commentable\Traits\Commentable as CommentableTrait;
use Spoowy\VideoAntHelper\VideoAntHelper;
use \Done\Subtitles\Subtitles;

class Video extends Model implements Commentable
{
    use CommentableTrait;

    protected $table = 'videos';

    /*protected $fillable = ['*'];*/
    protected $guarded = [];


    public static function boot()
    {
        self::created(function($model){
            VideoPlayerOption::updateOptions(array(), $model->id);
        });

        self::deleting(function($model){

            $model->videoDescription()->delete();

            $model->playerOptions()->delete();

            $model->playlists()->each(function($playlist) {
                $playlist->delete();
            });

            $model->resolutions()->each(function($resolution) {
                $resolution->delete();
            });

        });
    }


    public function playlists()
    {
        return $this->hasMany('App\PlaylistVideo', 'video', 'id');
    }

    /**
     * Attributes
     *
     */
    public function getStatsAttribute()
    {
        return StatisticsSummary::firstOrCreate(['video_id' => $this->id]);
    }

    public function getViewsAttribute()
    {
        return $this->stats->video_views;
    }

    public function getClicksAttribute()
    {
        return $this->stats->clicks;
    }

    public function getLeadsAttribute()
    {
        return $this->stats->email_capture;
    }

    public function getCreatedAtAttribute($value) {
        return time_according_to_user_time_zone($value);
    }

    public function getVisualWatermarkingAttribute($value){
        return (boolean) $value;
    }

    public  function getForensicWatermarkingAttribute($value){
        return (boolean) $value;
    }

    public function getUpdatedAtAttribute($value) {
        return time_according_to_user_time_zone($value);
    }

    public function setUpdatedAtAttribute($value) {
        $this->attributes['updated_at'] = time_according_to_user_time_zone($value);
    }
    
    /*public function getUpdatedAtFormattedAttribute()
    {
        return Carbon::parse($this->updated_at)->diffForHumans();
    }*/

    public function sharedSnap()
    {
        return $this->hasOne('App\SharedSnap');
    }

    /**
     * Returns video duration in seconds
     *
     * @param $value
     * @return mixed
     */
    public function getDurationAttribute($value)
    {
        if ($value == 0) {
            if (isset($this->info)) {
                $info = $this->info;
                if ($info) {
                    // If duration is zero, check it again
                    $durationInfo = VideoAntHelper::getDurationByVideoInformation($this->info);
                    if ($durationInfo) {
                        if (isset($durationInfo['duration']) && $durationInfo['duration'] != 0) {
                            // We got a duration
                            $value = $durationInfo['duration'];
                            $this->duration = $durationInfo['duration'];
                            $this->duration_formatted = $durationInfo['duration_formatted'];
                            // update in DB
                            $this->save();
                        }
                    }
                }
            }
        }
        return $value;
    }

    /**
     * Returns formatted duration
     *
     * @param $value
     * @return string
     */
    public function getDurationFormattedAttribute($value)
    {
        if ($value == 0) {
            $duration = $this->duration;
            if ($duration != 0) {
                $value = format_duration($duration);
                // store duration formatted in db
                $this->duration_formatted = $value;
            }
        }
        return $value;
    }

    /**
     * Returns all videos this user has access to
     *
     * @param bool $includeArchived
     * @return array
     */
    public static function getAllForUser($includeArchived = false)
    {
        $user = Auth::user();

        $projects = Project::getAllForTeam($user->currentTeam()->id, $includeArchived);

        $videos = collect();

        // Add videos to array
        $projects->each(function ($index) use (&$videos) {
            if ($index) {
                $projectVideos = $index->videos();
                if (count($projectVideos) > 0)
                    $projectVideos->each(function ($video) use (&$videos) {
                        $videos->push($video);
                    });
            }
        });

        return $videos;
    }

    public function getEmbedUrlAttribute()
    {
        return url('watch/' . $this->video_id);
        #return route('watchVideo', ['id' => $this->video_id]);
    }

    /**
     * Returns video source
     *
     * @return string
     */
    public function getSourceAttribute()
    {
        return VideoImport::getSource($this->path);
    }

    /**
     * Returns absolute path to video file
     *
     * If video is hosted on youtube, a youtube url is returned
     *
     * todo: untested
     *
     * @return mixed
     */
    public function getSrcAttribute()
    {
        return $this->mapCDNUrl($this->path);
    }

    public function getPathAttribute($value)
    {
	return $this->mapCDNUrl($value);
    }

    /**
     * Returns video quality files
     */
    public function getQualityFilesAttribute()
    {
        return $this->files();
    }

    /**
     * Returns URl to default thumbnail
     *
     * todo: untested
     *
     * @return string
     */
    public function getDefaultThumbnailAttribute()
    {
        $defaultThumbnail = VideoDefaultThumbnail::where('video_id', $this->id)->first();
        if ($defaultThumbnail) {
            return $defaultThumbnail->url;
        } else {
            return thumbnail_path($this->video_id . '/' . $this->video_id, true) . '_0.jpg';
        }
    }

    /**
     * Get the last custom thumbnail
     *
     * todo: untested
     *
     * @return bool|mixed
     */
    public function getCustomThumbnailAttribute()
    {
        $customThumbnail = false;

        $lastCustomThumbnail = VideoRelevantThumbnail::where('key', $this->video_id)
						     ->orderBy('id', 'DESC')->first();

        if (!empty($lastCustomThumbnail) && $this->thumbnail != $this->default_thumbnail) {

            if (isset($this->thumbnail) && isset($lastCustomThumbnail->url)) {
                if ($this->thumbnail != $lastCustomThumbnail->url) {
                    // We have a custom thumbnail set on the video
                    $imageObject = Image::getImageByUrl($this->thumbnail);
                    if ($imageObject) {
                        return $imageObject->url;
                    }
                } else {
                    return $lastCustomThumbnail->url;
                }
            }
        }

        return $customThumbnail;
    }

    /**
     * Returns cropped thumbnail if possible
     *
     * todo: untested
     *
     * @return mixed
     */
    public function getCroppedThumbnailAttribute()
    {
        if (!Image::isCroppable($this->thumbnail))
            return $this->thumbnail;
        $customThumbnail = Image::getImageByUrl($this->thumbnail);
        // Only crop if image is from our server, no external cropping possible
        if ($customThumbnail)
            // crop it
            return \Bkwld\Croppa\Facade::url(image_path($this->filename, true), env('STANDARD_THUMBNAIL_WIDTH'));
        return $this->thumbnail;
    }

    /**
     * Returns tiny thumbnail
     *
     * todo: untested
     *
     * @return mixed
     */
    public function getMiniThumbnailAttribute() // Should be called tiny
    {
        if ($customThumbnail = Image::getImageByUrl($this->thumbnail))
            // Crop it
            return \Bkwld\Croppa\Facade::url(image_path($customThumbnail->filename, true), 40, 40);

        return $this->thumbnail;
    }

    /**
     * Prepares thumbnail url
     *
     * @param $value
     * @return mixed|string
     */
    public function getThumbnailAttribute($value)
    {
        $thumbnail = Video::prepareThumbnailUrl($value);
        if ($thumbnail == '')
            $thumbnail = env('DEFAULT_THUMBNAIL');
        return $thumbnail;
    }

    /**
     * Returns Video Player Options
     *
     * @return \stdClass
     */
    public function getPlayerOptionsAttribute()
    {
        return VideoPlayerOption::getPlayerOptionsByVideoId($this->id);
    }

    /**
     * Url to scrumb file
     *
     * @return string
     */
    public function getScrumbAttribute()
    {
        $scrumb = $this->scrumb();
        if ($scrumb) {
            return $scrumb->url;
        }
        return '';
    }

    /**
     * Returns the title
     *
     * @return mixed|string
     */
    public function getTitleAttribute($value)
    {
        return (!empty($value) ? $value : urldecode($this->filename));
    }

    /**
     * Returns true if video is from external sources. E.g. YouTube / Vimeo
     *
     * @return bool
     */
    public function getImportedAttribute()
    {
        return ($this->filename == 'imported' ? true : false);
    }

    /**
     * Processing events
     *
     */

    /**
     * Renames a file on s3 for storage
     * @return Video
     * @throws \ErrorException
     * @internal param Video $video
     */
    public function renameOriginalS3File()
    {
        \Log::error('storage filename: '. $this->filename);

        \Log::error('storage file exists?: ' . \Storage::exists($this->filename));

        // Does this file exist on s3?
        if (!\Storage::exists($this->filename)) {

            // Sleep five seconds
            sleep(5);

            if (!\Storage::exists($this->filename)) {
                VideoProcessingEvent::fire('rename', $this, 'error');
                throw new \ErrorException('Video file upload failed');
            }
        }

        $videoInfo = $this->info;

        // Create a new filename
        $newFileName = $this->owner . '_original_' . $this->video_id . '.' . $this->extension;

        // RENAME THE VIDEO to video id
        try {

            if (!\Storage::exists($newFileName)) {
                $renamed = \Storage::rename(
                    $this->filename,
                    $newFileName
                );
            }

            // Update Video model: path and filename
            $this->path = env('VIDEO_STORAGE') . $newFileName;
            $this->filename = $newFileName;
            $this->save();

            // Store original file path in video_file_original
            $originalFileEntry = new VideoFileOriginal();
            $originalFileEntry->video_id = $this->video_id;
            $originalFileEntry->path = env('VIDEO_STORAGE') . $this->filename;
            $originalFileEntry->status = 'Processing';
            $originalFileEntry->save();

            // Fire success
            VideoProcessingEvent::fire('rename', $this, 'success');

        } catch (\Exception $e) {
            // Log renaming error
            \Log::error($e->getMessage(), (array)$e);
            // Fire error
            VideoProcessingEvent::fire('rename', $this, 'error');
        }

        return $this;
    }

    /**
     * Returns video extension
     *
     * @return mixed
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    /**
     * Transcodes video
     *
     * @return array
     */
    public function transcode()
    {
        return VideoTranscoder::transcode($this);
    }

    /**
     * Deletes the original video file
     */
    public function deleteOriginalFile()
    {
        return VideoFileOriginal::deleteOriginalFile($this);
    }

    public function generateDefaultThumbnail()
    {
        return Thumbnail::generateDefaultThumbnail($this);
    }

    public function generateScrumb()
    {
        return VideoThumbnailScrumb::generate($this);
    }

    /**
     * Unlocks the video while it was processing
     */
    public function unlock()
    {
        $originalFile = VideoFileOriginal::where('video_id', $this->video_id)->first();
        if (!$originalFile) {
            // Create original file
            $originalFile = new VideoFileOriginal();
            $originalFile->video_id = $this->video_id;
            $originalFile->path = $this->path;
        }
        $originalFile->status = 'Complete';
        $originalFile->save();

        VideoProcessingEvent::fire('unlock', $this, 'success');
    }

    /**
     * Prepares the thumbnail url
     *
     * Sets video ant url prefix if doesn't exit
     *
     * @param $url
     * @return string
     */
    public static function prepareThumbnailUrl($url)
    {
        if (starts_with($url, '/data/videos')) {
            return url($url);
        }
        if (!starts_with($url, ['/CTAMonkey', '/data'])) {
            // Dirty fix for server
            if (starts_with($url, '/'))
                $url = env('VIDEOANT_URL_PREFIX') . $url;

            if (starts_with($url, '/'))
                $url = 'https://' . $url;
        }
        return $url;
    }


    /**
     * Duplicates a video with quality files and relevant thumbnail dependencies
     *
     * @param $projectId
     * @return Model
     */
    public function duplicate($projectId)
    {
        $user = Auth::user();

        // Replicate
        $replicate = $this->replicate();
        $replicate->video_id = str_random(8);
        $replicate->project = ($projectId == 0 ? $this->project : $projectId);
        $replicate->owner = (isset($user->id) ? $user->id : $this->owner);
        $replicate->team = $user->currentTeam()->id;
        $replicate->title = $this->title . ' 2';
        $replicate->save();

        // Duplicate video quality file entries
        VideoQualityFile::duplicateQualityFile($this->video_id, $replicate->video_id);

        // Duplicate relevant thumbnails
        VideoRelevantThumbnail::duplicateRelevantThumbnails($this->video_id, $replicate->video_id);

        return $replicate;
    }

    /**
     * Returns video description
     *
     * @return mixed|string
     */
    public function description()
    {
        return VideoDescription::getDescription($this);
    }

    public function videoDescription()
    {
        return $this->hasOne('App\VideoDescription');
    }

    /**
     * Returns cta elements for video
     *
     * @return mixed
     */
    public function ctaElements()
    {
        return CtaElement::getCtaElementsByVideoId($this->id);
    }

    /**
     * Player options
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function playerOptions()
    {
        return $this->hasOne('App\VideoPlayerOption', 'video_id', 'id');
    }
    public function videoChapters()
    {
        return $this->hasMany('App\VideoChapter', 'video_id', 'id');
    }
    public function videoSubtitles()
    {
        return $this->hasOne('App\VideoSubtitle', 'video_id', 'id');
    }
    public function videoBasecode()
    {
        return $this->hasOne('App\VideoBasecode', 'video_id', 'id');
    }

    /**
     * Returns project
     *
     * @return mixed
     */
    public function project()
    {
        return Project::where('id', $this->project)->first();
    }

    public function videoProject()
    {
        return $this->belongsTo('App\Project', 'project');
    }

    public function snapPages()
    {
        return $this->hasMany('App\SnapPage');
    }

    /**
     * Returns team
     *
     * @return mixed
     */
    public function team()
    {
        return Spark::interact(TeamRepository::class . '@find', [$this->team]);
    }


    /**
     * Video File
     *
     * todo: untested
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function file()
    {
        return $this->hasOne('App\VideoQualityFile', 'video_id', 'video_id')
		    ->where('format', 480);
    }

    public function owner()
    {
        return $this->belongsTo('App\User', 'owner', 'id');
    }

    public function summary()
    {
        return $this->hasOne('App\StatisticsSummary', 'video_id', 'id');
    }

    public function bandwidth_records()
    {
        return $this->hasMany('App\BunnyCDNBandwidthRecords', 'video_id', 'video_id');
    }

    /**
     * List of files
     *
     * @var array
     */
    protected $fileList = [];

    /**
     * Returns an array of files
     *
     * todo: untested
     *
     * @return string
     */
    public function files()
    {
        if (empty($this->fileList)) {
            if ($this->source == 's3') {
                $fileList = VideoQualityFile::where('video_id', $this->video_id)->orderBy('format')->get();

                // Serve s3 url
                $this->fileList = $fileList->map(function ($index) {
                    #$index->path = AmazonUrl::deliverS3Url($index->path, $this);
                    return $index->path;
                });
            } else {
                $video = $this;
                $rawSources = self::getVideoSource($this->path, true, false, true);
                $this->fileList = collect($rawSources)->map(function ($item) use ($video) {
                    if (isset($item['url']) && !ends_with($item['url'], '/../')) {
                        $file = (object)[
                            'video_id' => $video->video_id,
                            'format' => $item['format'],
                            'path' => $item['url']
                        ];
                        return $file;
                    }
                })->filter()->sortBy('format');
            }
        }
        return $this->fileList;
    }

    /**
     * Returns path to lowest quality file
     *
     * todo: untested
     *
     * @return bool
     */
    public function getLowestQualityFileAttribute()
    {
        $files = collect($this->files());
        if (count($files)) {
            return $files->first();
        }
        return false;
    }

    /**
     * Returns path to middle quality file
     *
     * todo: untested
     *
     * @return array
     */
    public function getMiddleQualityFileAttribute()
    {
        // Returns 480p file.
        $files = collect($this->files());
        if (count($files)) {
            // TODO: We must consider the youtube and vimeo formats as well!
            return $files->filter(function ($file) {
                $format = intval($file->format);
                if ($format > 400 && $format < 600)
                    return true;
                return false;
            })->first();
        }
        return false;
    }

    /**
     * Returns path to highest quality file
     *
     * todo: untested
     *
     * @return bool
     */
    public function getHighestQualityFileAttribute()
    {
        $files = collect($this->files());
        if (count($files)) {
            return $files->last();
        }
        return false;
    }

    /**
     * Returns true if video transcoding is done
     *
     * @return mixed
     */
    public function getIsTranscodedAttribute()
    {
        $originalFile = $this->originalFile;
        if ($originalFile) {
            return $originalFile->isTranscoded();
        }
        // TODO: Not true for imported videos from the start
        return true;
    }

    /**
     * Returns true if video has been touched
     *
     * @return bool
     */
    public function getHasBeenTouchedAttribute()
    {
        return ($this->created_at == $this->updated_at ? false : true);
    }

    /**
     * Returns date in time elapsed string
     *
     * @return bool
     */
    public function getDateFormattedAttribute()
    {
        $user = auth()->user();
        return time_elapsed_string($this->updated_at, false, $user->settings->timezone);
    }

    /**
     * Returns date in time elapsed string
     *
     * @return bool
     */
    public function getCreatedDateFormattedAttribute()
    {
        $user = auth()->user();
        return time_elapsed_string($this->created_at, false, $user->settings->timezone);
    }

    /**
     * Returns VideoThumbnailScrumb object
     *
     * @return mixed
     */
    public function scrumb()
    {
        $path = $this->path;
        if (strpos($path, 'amazon') !== false) {
            $path = 'https://s3.amazonaws.com/ctamonkey/production/' . $this->video_id . '_240p.mp4';
        }
        return VideoThumbnailScrumb::where('path', $path)->first();
    }

    /**
     * Returns full attributes
     *
     * @return Video
     */
    public function full()
    {

        $video = $this;
        $video->duration = $this->duration;
        $video->cropped_thumbnail = $this->cropped_thumbnail;
        $video->quality_files = $this->quality_files;
        $video->player_options = $this->player_options;
        $video->video_chapters = $this->videoChapters;

        $owner = User::find($this->owner);
        $video->video_owner = $owner;
        $video->autoplay = $owner->settings->autoplay == 1 ? true : false ;
        $video->resume_player = $owner->settings->resume_player == 1 ? true : false ;
        $video->pause_player = $owner->settings->pause_player == 1 ? true : false ;
        $video->sticky_player = $owner->settings->sticky_player == 1 ? true : false ;

        $video->video_subtitles = $this->videoSubtitles !== null ? $this->videoSubtitles->translatedSubtitles : $this->videoSubtitles;

        $video->video_basecodes = $this->videoBasecode;
        $video->video_eventcodes = $this->videoBasecode !== null ? $this->videoBasecode->eventCode : [];
//        $video->video_basecodes = $this->videoBasecode !== null ? $this->videoBasecode->eventCode : $this->videoBasecode;
        $video->translated_srt_caption = ['captions' => [], 'id' => '', 'type' => null];
        $video->embed_url = $this->embed_url;
        $video->source = $this->source;
        $video->src = $this->src;
        $video->default_thumbnail = $this->default_thumbnail;
        $video->custom_thumbnail = $this->custom_thumbnail;
        $video->project = $this->project();
        $video->description = $this->description();
        $video->resolutions = $this->resolutions;
        $video->published_on_stage = ($video->published_on_stage == 'true' ? true : false);
        $video->featured_on_stage = ($video->featured_on_stage == 'true' ? true : false);
        $video->files = collect(json_decode($video->files))->map(function($value) {return $value;})->unique();
        $video->thumbnails = collect(json_decode($video->thumbnails))->unique();
        $video->download_url = collect(json_decode($video->files))->sort()->last();
        // TODO: Need to set HLS file
        // TODO: Need to set vtt file!
        return $this->mapCDNUrls($video);
    }

    public function mapCDNUrls(Video $video)
    {
        $video->files = $video->files->map(function ($file) { return $this->mapCDNUrl($file); })->filter(function ($string) { return $string != "";});
        $video->thumbnail = $this->mapCDNUrl($video->thumbnail, true);
	$video->download_url = $this->mapCDNUrl($video->download_url);
	$video->thumbnails = $video->thumbnails->map(function ($file) { return $this->mapCDNUrl($file); })->filter(function ($string) { return $string != ""; });
	$video->dash_url_drm = $this->mapCDNUrl($video->dash_url_drm);
	$video->hls_url_aes = $this->mapCDNUrl($video->hls_url_aes);
        return $video;
    }

    protected function mapCDNUrl($url, $skip = false)
    {
        if ($skip) {
            return $url;
        }
        $cdnHost = "stream.adilo.com";
        $parsedUrl = parse_url($url);
	$path = preg_replace('/^(\/+)/', '', $parsedUrl['path']);
        if (isset($parsedUrl['host']))
            if (strpos($parsedUrl['host'], "wasabi"))
                $url = "https://" . $cdnHost . "/" . $path . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
	return $url;
    }


    public function resolutions()
    {
        return $this->hasMany(VideoResolution::class, 'video_id', 'id');
    }


    /**
     * Returns video source file for youtube/vimeo urls via youtube-dl
     *
     * @param $url
     * @param bool $multipleFormats
     * @param bool $lowestQuality
     * @param bool $onlyMp4Format
     * @return string
     */
    public static function getVideoSource($url, $multipleFormats = false, $lowestQuality = false, $onlyMp4Format = false)
    {
        $lowestQuality = true;
        $source = VideoImport::getSource($url); // youtube | vimeo | s3

        $command = env('YOUTUBE-DL_PATH') . ' "' . $url . '" --no-warnings ';

        if ($lowestQuality) {
            if ($source == "vimeo")
                $command .= '-f "http-240p" '; // http-240 format
            if ($source == "youtube")
                $command .= '-f "160" '; // 160 yt format
            if ($source != "s3") {
                $output = exec($command . "-g"); // -g = get url
                return $output;
            }
        }
        if ($multipleFormats) {
            // Command, get json information
            $command .= "-j "; // json

            if ($onlyMp4Format && $source == "youtube")
                $command .= '-f "[ext=mp4]" ';
            if ($onlyMp4Format && $source == "vimeo")
                $command .= '-f "[ext=mp4]" '; // TODO: Vimeo doesn't work

            $output = exec($command);

            $json = json_decode($output);

            if (isset($json->formats)) {
                foreach ($json->formats as $format) {
                    if (isset($format->width) && isset($format->url)) {

                        // We cannot accept m3u8 extensions
                        if (strpos($format->url, 'm3u8') === false) {
                            /*if ($lowestQuality) {
                               // Returns lowest quality
                               if (VideoImport::isVimeoUrl($url)) {
                               // Vimeo url, respond on first url
                               if (!ends_with($format->url, '../'))
                               return $format->url;
                               } else {
                               // Return first format where width is greater than 300, on youtube videos
                               if ($format->width > 300) {
                               return $format->url;
                               }
                               }
                               }*/
                            // Add to video formats output array
                            $return[] = [
                                'format' => $format->width,
                                'url' => $format->url
                            ];
                        }
                    }
                }
                // Return all formats
                return $return;
            }
        }
        // Return only one url
        if ($source == "vimeo")
            $command .= '-f "http-360p" ';
        if ($source == "youtube")
            $command .= '-f "[ext=mp4]" ';
        $command .= '-g';
        return exec($command);
    }


    /**
     * Returns true if video->src is a youtube url
     *
     * @return bool
     */
    public function isYoutube()
    {
        return VideoImport::isYoutubeUrl($this->src);
    }

    /**
     * Updates used storage size
     *
     * @return mixed
     */
    public function updateStorageInfo()
    {
        return VideoFileStorage::updateStorageInfoForVideo($this);
    }

    /**
     * Returns original file
     *
     * @return mixed
     */
    public function originalFile()
    {
        return $this->hasOne('\App\VideoFileOriginal', 'video_id', 'video_id');
    }

    public function log() {
        return $this->hasMany('\App\BunnyCDNBandwidthRecords', 'video_id', 'video_id');
    }


    /**
     * duplicate/copy all files related video from wasabi bucket and create new record in the database
     *
     * @param $data
     * @return array
     */
    public static function copyDuplicateVideo($data)
    {
        if (isset($data['video_id']) && !is_null($data['video_id']))
        {
            /* get video model */
            $video = Video::with('videoDescription', 'playlists', 'resolutions', 'playerOptions')
			  ->where(['video_id' => $data['video_id']])
			  ->orWhere(['id' => $data['video_id']])
			  ->first();

            /* check if model exists */
            if (!is_null($video))
            {
                $bucket = config('aws.bucket');
                $endpoint = config('aws.endpoint');

                /* get s3 client */
                $s3 = S3Client::factory(array(
                    'endpoint' => $endpoint,
                    'region' => config('aws.region'),
                    'version' => config('aws.version'),
                    'credentials' => config('aws.credentials')
                ));

                /* find owner and generate folder name */
                $owner = User::find($video->owner);
                $owner_folder = generate_owner_folder_name($owner->email);
                $file_key = "{$owner_folder}/{$video->video_id}/";

                /* generate new video id and key */
                $new_video_id = generate_video_unique_id();
                $new_file_key = "{$owner_folder}/{$new_video_id}";

                try {
                    /* try to get file objects */
                    $objects = $s3->getIterator('ListObjects', array(
                        'Bucket' => $bucket,
                        'Prefix' => "$file_key"
                    ));
                } catch (\Exception $exception) {
                    return ['success' => false, 'message' => $exception->getMessage()];
                }

                $files = [];

                /* copy all files recursively */
                foreach ($objects as $object) {
                    $new_file_name = str_replace("$file_key", '', $object['Key']);

                    $files[] = $s3->getCommand('CopyObject', [
                        'Bucket' => $bucket,
                        'Key' => "{$new_file_key}/{$new_file_name}",
                        'CopySource' => "{$bucket}/{$object['Key']}",
                    ]);
                }

                if (is_array($files) && !empty($files)) {
                    try {
                        /* put copied files to new generated folder */
                        CommandPool::batch($s3, $files);

                        /* create new video record in database with relations */
                        $new_video = $video->replicate();
                        $new_video->video_id = $new_video_id;
                        $event = 'duplicated';
                        if (isset($data['video_title']) && !is_null($data['video_title'])) {
                            $new_video->title = $data['video_title'];
                        }
                        if (isset($data['copied_project_id']) && !is_null($data['copied_project_id'])) {
                            $new_video->project = $data['copied_project_id'];
                            $event = 'copied';
                        }
                        $new_video->save();

                        return ['success' => true, 'new_video' => $new_video, 'message' => "Your video has been {$event}."];
                    } catch (\Exception $e) {
                        return ['success' => false, 'message' => $e->getMessage()];
                    }
                } else {
                    return ['success' => false, 'message' => 'No files found in bucket'];
                }
            }

            return ['success' => false, 'message' => 'There is no video with the requested id'];
        }

        return ['success' => false, 'message' => 'You must send video id'];
    }


    public static function uploadFileToBucket($file_key, $file_path, $file_size)
    {
        $endpoint = config('aws.endpoint');
        $bucket = config('aws.bucket');

        $s3 = S3Client::factory(array(
            'endpoint' => $endpoint,
            'region' => config('aws.region'),
            'version' => config('aws.version'),
            'credentials' => config('aws.credentials')
        ));

        try {
            $s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $file_key,
                'Body' => file_get_contents($file_path),
                'ContentLength' =>$file_size,
                'ACL' => 'public-read'
            ));

            $file_path = $s3->getObjectUrl($bucket, $file_key);

            return ['success' => true, 'file_path' => $file_path];
        } catch (\Exception $exception){
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }


    public static function deleteAllBucketFiles($video_unique_id, $owner_id)
    {
        $bucket = config('aws.bucket');
        $endpoint = config('aws.endpoint');

        /* get s3 client */
        $s3 = S3Client::factory(array(
            'endpoint' => $endpoint,
            'region' => config('aws.region'),
            'version' => config('aws.version'),
            'credentials' => config('aws.credentials')
        ));

        /* find owner and generate folder name */
        $owner = User::find($owner_id);
        $owner_folder = generate_owner_folder_name($owner->email);
        $file_key = "{$owner_folder}/{$video_unique_id}/";

        /* try to delete objects */
        try {
            $s3->deleteMatchingObjects($bucket, $file_key);
            return ['success' => true, 'message' => 'Your video files deleted successfully.'];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }


    public static function deleteAllStatistics($video_id)
    {
        return Statistic::where(['video_id' => $video_id])->delete();
    }

      /**
     * get video views count
     *
     * @param $video_id
     * @return mixed
     */
    public function view()
    {
        return $this->hasMany('App\Statistic', 'video_id', 'id');
    }
    
    public function lostExperiment()
    {
        return $this->hasMany('App\Experiment\VideoExperimentWinner');
    }

    public static function ifActiveExperiment($video, $cookie)
    {
        $experiment = ThumbnailExperiment::where([
            'video_id' => $video->id,
            'active'   => 1
        ])->first();
        if ($experiment) {
            $cookieData = ExperimentBrowserCookie::where('experiment_id', $experiment->id)
                ->where('experiment_type', 'thumbnail')
                ->where('cookie', $cookie)->first();
            // 50% chance of one of the two thumbnails.
            if (isset($cookieData->id) && $cookieData->experiment_type == 'thumbnail') {
                $randomClick = ThumbnailClickCount::find($cookieData->thumbnail_video_id);
            } else {
                $lastEntry = ExperimentBrowserCookie::where([
                    'experiment_type' => 'thumbnail',
                    'experiment_id' => $experiment->id
                ])->orderBy('id', 'DESC')->first();

                if (isset($lastEntry->thumbnail_video_id)) {
                    $randomClick = ThumbnailClickCount::where('experiment_id', $experiment->id)
                        ->where('id', '!=', $lastEntry->thumbnail_video_id)->first();
                } else {
                    $randomClick = ThumbnailClickCount::where('experiment_id', $experiment->id)->first();
                }
                ExperimentBrowserCookie::create([
                    'experiment_type' => 'thumbnail',
                    'experiment_id'   => $experiment->id,
                    'cookie'          => $cookie,
                    'thumbnail_video_id' => $randomClick->id
                ]);
            }

            if ($randomClick) {
                $video->thumbnail = $randomClick->url;
                $video->experiment_id = $experiment->id;
                $video->experiment_click_id = $randomClick->id;
                $video->experiment_overlay = $randomClick->overlay_text;
            }
        }
        $video->video_experiment_id = self::videoExperimentExists($video->id);
        if ($video->video_experiment_id || $video->experiment_id) return $video;
        else return false;
    }

    public static function videoExperimentExists($id)
    {
        $exists = VideoExperiment::where('active', 1)
        ->where(function ($q) use ($id) {
            $q->where('video_id_a', $id);
            $q->orWhere('video_id_b', $id);
        })->first();
        return $exists ? @$exists->id : false;
    }

    public static function videoExperimentShuffle($videoId)
    {
        $id = false;
        $ex = VideoExperiment::find($videoId);
        
        if ($ex) {
            if ($ex->active == 1) {
                $owner = User::find(self::find($ex->video_id_a)->owner);
                $cookie = self::storeRetrieveCookie($owner, $ex);
                $id = $cookie['video_id'];
            } else {
                $winner = $ex->winner;
                if ($winner) $id = $winner->winner_id;
            }
        }
        if ($id) {
            return Video::find($id);
        }
        return false;
    }

    public static function filterVideos($filter = [], $snap = false)
    {
        // Get videos
        $user = auth()->user();
        $videos =  Video::where('owner', $user->id);
        if ($snap) {
            $videos = $videos->where(function ($q) {
                $q->where('video_type', 2);
            });
        }
        $videos = $videos->orderBy('id', 'DESC');
        $videos = $videos->get();

        foreach ($videos as $i => $video) {
            $videos[$i]['views_count'] = Statistic::where([
                'domain' => config('app.site_domain'),
                // 'project_id' => $this->id,
                'video_id' => $video->id,
                'event' => 'video_view'
            ])->count();

            $videos[$i]['clicks_count'] = Statistic::where([
                'domain' => config('app.site_domain'),
                // 'project_id' => $this->id,
                'video_id' => $video->id,
                'event' => 'click'
            ])->count();
        }

        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                $value = (array) $value;
                switch ($key) {
                    case 'date':
                    {
                        $videos = Project::filterDate($value, $videos);
                        break;
                    }
                    case 'title':
                    {
                        $videos = Project::filterTitle($value, $videos);
                        break;
                    }
                    case 'views':
                        {
                            $videos = Project::filterViews($value, $videos, 'videos');
                            break;
                        }
                    case 'clicks':
                        {
                            $videos = Project::filterClicks($value, $videos, 'videos');
                            break;
                        }
                    case 'leads':
                        {
    //                            $videos = self::filterLeads($value, $videos, 'videos');
                            break;
                        }
                }
            }
        }



        return $videos->map(function ($index) {
                $description = VideoDescription::where(['video_id' => $index->id])->first();

                $newIndex = $index;
                // Init get dynamic variables
                $newIndex->has_been_touched = $index->has_been_touched;
                $newIndex->date_formatted = $index->date_formatted;
                $newIndex->updated_at_formatted = $index->date_formatted;
                $newIndex->clicks = $index->clicks;
                $newIndex->views = $index->views;
                $newIndex->leads = $index->leads;
                $newIndex->is_imported = $index->is_imported;
                $newIndex->imported = $index->imported;
                $newIndex->scrumb = $index->scrumb;
                $newIndex->duration_formatted = $index->duration_formatted;
                $newIndex->description = !is_null($description) ? $description->description : '';

                $project = Project::find($index->project);
                $newIndex->project = $project;

                return $newIndex;
            })->values();
    }

    function schedular(){
        return $this->hasOne('App\VideoPublishSchedular', 'video_id', 'id');
    }

    public static function storeRetrieveCookie($owner, $ex)
    {
        $cookie_name = 'user-video-cookie';
        $cookie_value = md5(rand(0, 15));
        $cookieToSave = '';

        if (!isset($_COOKIE[$cookie_name])) {
            setcookie($cookie_name, $cookie_value, time() + 31556926, "/");
            $_COOKIE[$cookie_name] = $cookie_value;
            $cookieToSave = $cookie_value;
        } else {
            $cookieToSave = $_COOKIE[$cookie_name];
        }
        $savedCookie = ExperimentBrowserCookie::where([
                'experiment_type' => 'videos',
                'cookie'          => $cookieToSave,
                'experiment_id'   => $ex->id
            ])->first();

        $ids = [$ex->video_id_a, $ex->video_id_b];
        if (!isset($savedCookie->id)) {
            
            $lastUsed = ExperimentBrowserCookie::where([
                'experiment_type' => 'videos',
                'experiment_id' => $ex->id
            ])->orderBy('id', 'DESC')->first();

            if (isset($lastUsed->id)) {
                foreach ($ids as $item) {
                    if ($item != $lastUsed->thumbnail_video_id) {
                        $video_id = $item;
                    }
                }
            } else {
                $video_id = $ids[array_rand($ids)];
            }
            $savedCookie = ExperimentBrowserCookie::create([
                'experiment_type' => 'videos',
                'cookie'          => $cookieToSave,
                'thumbnail_video_id' => $video_id,
                'experiment_id' => $ex->id
            ]);
            $id = $savedCookie->thumbnail_video_id;
        } else {
            $id = $savedCookie->thumbnail_video_id;
        }
        return ['video_id' => $id];
    }

    public static function parseSub($file)
    {
        $file_as_array = file($file);
        $data = [];
        $string = implode('', $file_as_array);

        $subtitles = Subtitles::load($string, 'vtt');
        $blocks = $subtitles->getInternalFormat(); // array
        $data = [];
        foreach ($blocks as $block) {
            $start = number_format($block['start']);
            foreach ($block['lines'] as $line) {
                $data[$start] = $line;
            }
        }
        \Log::info($data);
        return $data;
    }

    public static function convertChaptersToSeconds($chapters)
    {
        $chapters = $chapters->toArray();
        $time = array_map(function($chapter) {
            $timestamp = $chapter['time'];
            $d = explode(':', $timestamp);
            return ($d[0] * 3600) + ($d[1] * 60) + $d[2];
        }, $chapters);
        return $time;
    }

    public static function durationDivided($duration)
    {
        $a = $duration;
        $b = $duration / 5;
        $divided = $a/$b;
        $c = array();
        $reminder = $a/$b - floor($a/$b);
        for($i = 1; $i <= floor($divided); $i++){
            $c[] = number_format($b * $i);
        }
        return $c;
    }

    public static function bestVideoFile($video)
    {
        $files = array_reverse($video->files->toArray());
        foreach ($files as $file) {
            if (strpos($file, '480p')) {
                return $file;
            } elseif (strpos($file, '360p')) {
                return $file;
            } elseif (strpos($file, '240p')) {
                return $file;
            } elseif (strpos($file, '144p')) {
                return $file;
            }
        }
    }

    public static function getVideoObject($row, $user)
    {
        $video = Video::find($row->id);
        if (!$video->duration && ($video->duration_formatted === '' || is_null($video->duration_formatted))) {
            $result = shell_exec('ffmpeg -i ' . escapeshellcmd($video->path) . ' 2>&1');
            preg_match('/(?<=Duration: )(\d{2}:\d{2}:\d{2})\.\d{2}/', $result, $match);
            if (isset($match[1])) {
                $duration = $match[1];
                $video->duration_formatted = $match[1];

                $duration = explode(':', $duration);
                $duration = $duration[0] * 60 * 60 + $duration[1] * 60 + $duration[2];
                $duration = round($duration);
                $video->duration = $duration;

                $video->save();
            }
        }

        $obj = $row;
        $obj->full = $video->full();
        $obj->made_at = time_elapsed_string($video->created_at, false, $user->settings->timezone);
        $obj->published_on = date('M j, Y', strtotime($video->created_at));

        $owner = User::find($video->owner);
        $obj->owner_id = $owner->id;
        $obj->owner_name = $owner->name;
        $obj->owner_photo = $owner->photo_url;
        $obj->owner_logo = $owner->settings->logo;
        $obj->owner_stages = $owner->stages;
        $obj->comments = $video->comments()->whereNull('parent_id')->get()->map(function ($el) {
            $el = Comments::clearComments($el);

            $el->commented_at = date('M j, Y', strtotime($el->created_at));
            $el->showReplyInput = false;
            $el->showReply = false;

            $el->children->map(function ($cEl) {
                $cEl->commented_at = date('M j, Y', strtotime($cEl->created_at));

                return $cEl;
            });

            return $el;
        });

        return $obj;
    }

}
