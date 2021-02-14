<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoPlayerOption extends Model
{
    protected $table = 'video_player_options';

    protected $fillable = ['video_id',
        'permissions',
        'control_visibility',
        'autoplay',
        'speed_control',
        'quality_control',
        'settings',
        'volume_control',
        'share_control',
        'fullscreen_control',
        'playback',
        'branding_active',
        'thumbnail_video_url',
        'thumbnail_image_url',
        'text_overlay',
        'text_overlay_text',
        'color',
        'thumbnail_type',
        'allow_download',
        'permissions',
        'password',
        'password_button_text',
        'private_link',
        'embed_settings',
        'whitelisted_domains',
        'commenting_permissions',
        'redirect_url',
        'pixel_tracking',
        'interaction_before_email_capture',
        'interaction_before_email_capture_type',
        'interaction_before_email_capture_firstname',
        'interaction_before_email_capture_lastname',
        'interaction_before_email_capture_phone_number',
        'interaction_before_email_capture_allow_skip',
        'interaction_before_email_capture_upper_text',
        'interaction_before_email_capture_lower_text',
        'interaction_before_email_capture_button_text',
        'interaction_before_email_capture_email_list',
        'interaction_before_email_capture_email_tags',
        'interaction_during_time',
        'interaction_during_active',
        'interaction_during_type',
        'interaction_during_allow_skip',
        'interaction_during_text',
        'interaction_during_image',
        'interaction_during_link_url',
        'interaction_during_html_code',
        'interaction_during_email_capture',
        'interaction_during_email_capture_time',
        'interaction_during_email_capture_type',
        'interaction_during_email_capture_firstname',
        'interaction_during_email_capture_lastname',
        'interaction_during_email_capture_phone_number',
        'interaction_during_email_capture_allow_skip',
        'interaction_during_email_capture_upper_text',
        'interaction_during_email_capture_lower_text',
        'interaction_during_email_capture_button_text',
        'interaction_during_email_capture_email_list',
        'interaction_during_email_capture_email_tags',
        'interaction_after_type',
        'interaction_after_cta_type',
        'interaction_after_cta_text',
        'interaction_after_cta_image',
        'interaction_after_cta_html_code',
        'interaction_after_cta_link_url',
        'interaction_after_more_videos_text',
        'interaction_after_more_videos_list',
        'interaction_after_email_capture',
        'interaction_after_email_capture_type',
        'interaction_after_email_capture_firstname',
        'interaction_after_email_capture_lastname',
        'interaction_after_email_capture_phone_number',
        'interaction_after_email_capture_allow_skip',
        'interaction_after_email_capture_upper_text',
        'interaction_after_email_capture_lower_text',
        'interaction_after_email_capture_button_text',
        'interaction_after_email_capture_email_list',
        'interaction_after_email_capture_email_tags', 'chapter_control', 'subtitle_control',
        'visual_watermark_ip',
        'visual_watermark_timestamp',
        'visual_watermark_email',
        'visual_watermark_name',
        'geo_location',
        'deter_text',
        'deterText',
    ];

    public static $defaults = [
        'control_visibility' => 'on_hover',
        'autoplay' => false,
        'speed_control' => true,
        'quality_control' => true,
        'settings' => true,
        'volume_control' => true,
        'share_control' => false,
        'fullscreen_control' => true,
        'branding_active' => true,
        'playback' => false,
        'color' => '#25a0d9',
        'thumbnail_type' => 'image',
        'text_overlay' => false,
        'allow_download' => false,
        'permissions' => 'any',
        'embed_settings' => 'any',
        'commenting_permissions' => 'any',
        'redirect_url' => '',
        'pixel_tracking' => '',
        'password' => '',
        'whitelisted_domains' => '*',
        'interaction_before_email_capture' => false,
        'interaction_before_email_capture_type' => 'full',
        'interaction_before_email_capture_firstname' => 'true',
        'interaction_before_email_capture_lastname' => 'true',
        'interaction_before_email_capture_phone_number' => 'false',
        'interaction_before_email_capture_allow_skip' => 'true',
        'interaction_before_email_capture_upper_text' => 'Enter your email address to watch this video.',
        'interaction_before_email_capture_lower_text' => 'This is an example of the lower text.
Users can use it for privacy information etc.',
        'interaction_before_email_capture_button_text' => 'Play video',
        'interaction_during_time' => 0,
        'interaction_during_active' => false,
        'interaction_during_type' => 'text',
        'interaction_during_allow_skip' => 'true',
        'interaction_during_email_capture' => false,
        'interaction_during_email_capture_time' => 0,
        'interaction_during_email_capture_type' => 'full',
        'interaction_during_email_capture_firstname' => 'true',
        'interaction_during_email_capture_lastname' => 'true',
        'interaction_during_email_capture_phone_number' => 'false',
        'interaction_during_email_capture_allow_skip' => 'true',
        'interaction_during_email_capture_upper_text' => 'Enter your email address to watch this video.',
        'interaction_during_email_capture_lower_text' => 'This is an example of the lower text.
Users can use it for privacy information etc.',
        'interaction_during_email_capture_button_text' => 'Play video',
        'interaction_after_type' => 'more_videos',
        'interaction_after_more_videos_text' => 'Watch Related Videos',
        'interaction_after_cta_type' => 'text',
        'interaction_after_cta_text' => '',
        'interaction_after_cta_image' => '',
        'interaction_after_cta_link_url' => '',
        'interaction_after_email_capture' => 'false',
        'interaction_after_email_capture_type' => 'full',
        'interaction_after_email_capture_firstname' => 'true',
        'interaction_after_email_capture_lastname' => 'true',
        'interaction_after_email_capture_phone_number' => 'false',
        'interaction_after_email_capture_allow_skip' => 'true',
        'interaction_after_email_capture_upper_text' => 'Enter your email address to watch this video.',
        'interaction_after_email_capture_lower_text' => 'This is an example of the lower text.
Users can use it for privacy information etc.',
        'interaction_after_email_capture_button_text' => 'Play video',
        'chapter_control' => false,
        'subtitle_control' => false,
        'visual_watermark_ip' => false,
        'visual_watermark_timestamp' => false,
        'visual_watermark_email' => false,
        'visual_watermark_name' => false,
        'geo_location' => false,
        'deter_text' => false,
        'deterText' => 'Do not attempt to copy or share, your stream is being monitored',
    ];

    protected $primaryKey = 'id';

    public function __construct(array $attributes = [])
    {
        $this->setRawAttributes(self::$defaults, true);
        parent::__construct($attributes);
    }

    /**
     * Updates and creates player options
     *
     * @param $attributes
     * @param $videoId
     * @return mixed
     */
    public static function updateOptions($attributes, $videoId)
    {
        $attributes = array_merge(self::$defaults, $attributes);
        $attributes = collect($attributes)->map(function ($value) {
            if ($value === true)
                return 'true';
            if ($value === false)
                return 'false';
            if (is_array($value))
                return json_encode($value);
            return $value;
        })->toArray();
        $attributes['video_id'] = $videoId;

        if (env('APP_DEBUG'))
            \Log::info(json_encode($attributes));

        return VideoPlayerOption::updateOrCreate(['video_id' => $videoId], $attributes);
    }

    public static function getPlayerOptionsByVideoId($videoId)
    {
        $playerOptions = VideoPlayerOption::firstOrNew(['video_id' => $videoId], ['video_id' => $videoId]);

        // fix true and false stored values
        return collect($playerOptions)->map(function ($value, $key) {
            if (in_array($key, ['interaction_during_time', 'interaction_during_email_capture_time']))
                if ($value == 'true' || $value == 'false')
                    return 0;
            if ($value == 'true')
                return true;
            if ($value == 'false')
                return false;
            if (is_json($value))
                return json_decode($value, true);
            return $value;
        });
    }

    public function stats()
    {
        return $this->hasMany('App\Statistic', 'video_id', 'video_id');
    }
}
