<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Request;

class UserSettings extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'user_id',
        'user_image',
        'storage_space',
        'timezone',
        'currency',
        'street_address',
        'apartment_suite',
        'city',
        'state',
        'country',
        'zip_code',
        'company',
        'comments_my_video',
        'shares_my_video',
        'download_my_video',
        'email_captured',
        'bandwidth_exceeded',
        'stage_visibility',
        'stage_public_url',
        'public_stage_visited',
        'show_email_capture',
        'notify_to_subscribers',
        'auto_tag_email_list',
        'integration_id',
        'email_list_id',
        'stage_tags',
        'stage_name',
        'dashboard_settings',
        'label_receipts',
        'bill_estimates',
        'notify_email',
        'description',
        'bonus_bandwidth',
        'restricted_countries',
        'resume_player',
        'pause_player',
        'sticky_player',
        'autoplay',
        'default_resolution',
    ];

    protected $hidden = ['user'];

    /**
     * Create default settings
     *
     * @param $user
     * @return UserSettings
     */
    public static function createDefaultSettings($user, $timezone = 'America/New_York', $ip = '::1')
    {
        $country_city = explode('/', $timezone);

        $location = geoip()->getLocation($ip);

        $settings = UserSettings::where('user_id', $user->id)->first();
        if (!$settings) {
            $settings = new UserSettings();
        }
        $settings->user_id = $user->id;
        $settings->user_image = self::getDefaultAvatarUrl($user);
        $settings->street_address = '';
        $settings->apartment_suite = '';
        $settings->city = isset($location['city'])?$location['city']:'';
        $settings->state = isset($country_city[1]) ? $country_city[1] : '';
        $settings->country = $location['iso_code'];
        $settings->zip_code = '';
        $settings->stage_visibility = 'publish';
        $settings->stage_name = $user->name;
        $settings->timezone = $timezone;
        $settings->currency_format = 'USD';
        $settings->storage_space = 0;
        $settings->dashboard_settings = json_encode([]);
        $settings->notify_email = $user->email;
        $settings->resume_player = 1;
        $settings->pause_player = 1;
        $settings->sticky_player = 0;
        $settings->autoplay = 1;
        $settings->save();

        return $settings;
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Returns user avatar or default image
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        if (!$this->user->photo_url) {
            // return initials
            return self::getDefaultAvatarUrl($this->user);
        }

        return $this->user->photo_url;
    }

    /**
     * Returns url to user image
     *
     * @param $value
     * @return string
     */
    public function getUserImageAttribute($value)
    {
        if ($value != '') {
            $url = \Bkwld\Croppa\Facade::url($value, 150, 150);
            if (starts_with($url, '/data/profiles'))
                $url = config('env.ROOT_URL') .'/'.$url;
            $value = $url;
        }

        return $value;
    }

    /**
     * Returns default avatar url
     *
     * @param bool $user
     * @param string $extension
     * @return string
     */
    private static function getDefaultAvatarUrl($user = false, $extension = 'png')
    {
        if (!$user) {
            $user = Auth::user();
        }

        $initials = User::getInitials($user);

        return asset('data/initials/' . $initials . '.' . $extension);
    }

    /**
     * Resets avatar to default
     *
     * @param bool $user
     * @return string
     */
    public static function resetAvatar($user = false)
    {
        if (!$user) {
            $user = Auth::user();
        }

        $user->photo_url = self::getDefaultAvatarUrl($user);
        $user->save();

        return $user->photo_url;
    }
}
