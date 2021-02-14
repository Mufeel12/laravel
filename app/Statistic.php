<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Soumen\Agent\Facades\Agent;

class Statistic extends Model
{
    protected $table = 'statistics';

    /**
     * Mass assignment attributes
     * @var array
     */
    protected $fillable = [
        'id',
        'ip_address',
        'created_at',
        'updated_at',
        'video_id',
        'project_id',
        'user_id',
        'team_id',
        'event',
        'cookie',
        'unique_ref',
        'agents',
        'kind',
        'model',
        'platform',
        'platform_version',
        'is_mobile',
        'browser',
        'domain',
        'latitude',
        'longitude',
        'country_code',
        'country_name',
        'city',
        'event_offset_time',
        'event_interaction_group',
        'watch_start',
        'watch_end',
        'watch_session_id',
        'experiment_id',
        'experiment_click_id',
        'video_experiment_id'
    ];

    /**
     * Boot model
     */
    public static function boot()
    {

        parent::boot();

        /**
         * Model saved event
         */
        static::saved(function ($statistics) {
            // TODO: Update summary statistics
            // $summary = StatisticsSummary::firstOrCreate([
            //     'video_id' => $statistic->video_id,
            //     'project_id' => $statistic->project_id,
            //     'team_id' => $statistic->team_id,
            //     'total_actions_taken' => 0,
            //     'video_total_watch_time' => 0,
            //     'video_views' => 0,
            //     'video_skipped_aheads' => 0,
            //     'skipped' => 0,
            //     'clicks' => 0,
            //     'email_captures' => 0
            // ]);
            // $summary->increment('total_actions_taken');
        });
    }

    /**
     * Record Statistics Event
     * @param $attributes
     * @param $request
     */
    public static function record_event($attributes, $request)
    {
        $ip = $request->getClientIp();

        $browser = Agent::browser();
        $platform = Agent::platform();
        $device = Agent::device();
        $geo_location = geoip()->getLocation($ip);
        $location = $geo_location['city'] . ', ' . $geo_location['iso_code'];

        if ($request->hasCookie('user-cookie')) {
            $unique_id = $request->cookie('user-cookie');
        } else {
            $requestData = sprintf('lang:%s,ua:%s,ip:%s,accept:%s,ref:%s,encode:%s,location:%s',
                implode(',', $request->getLanguages()), $request->header('user-agent'), $ip,
                $request->header('accept'), $request->header('referer'), implode(',', $request->getEncodings()),
                $location);

            $unique_id = md5($requestData);
        }

        $defaultAttributes = [
            'ip_address'       => $ip,
            'cookie'           => $request->hasCookie('user-cookie') ? $request->cookie('user-cookie') : '',
            'unique_ref'       => $unique_id,
            'agents'           => $request->header('user-agent'),
            'kind'             => $device->getIsDesktop() ? 'Desktop' : ($device->getIsTablet() ? 'Tablet' : ($device->getIsMobile() ? 'Mobile' : 'Unknown')),
            'model'            => $device->getModel(),
            'platform'         => $platform->getName(),
            'platform_version' => $platform->getVersion(),
            'is_mobile'        => (string)(int)$device->getIsMobile(),
            'browser'          => $browser->getName(),
            'domain'           => $request->getHttpHost(),
            'latitude'         => $geo_location->lat,
            'longitude'        => $geo_location->lon,
            'country_code'     => $geo_location->iso_code,
            'country_name'     => $geo_location->country,
            'city'             => $geo_location->city
        ];
        
        $attributes = array_merge($defaultAttributes, $attributes);
        Statistic::create($attributes);
    }

    /**
     * @param $objectType
     * @param $objectId
     * @param $action
     * @param bool $ipAddress
     * @return bool
     */
    public static function storeAction($objectType, $objectId, $action, $ipAddress = false)
    {
        // $statistic = new Statistic();
        // $statistic->object_type = $objectType;
        // $statistic->object_id = $objectId; # can be cta element id, or video id
        // $statistic->action = $action;
        // $statistic->ip_address = ($ipAddress === false ? (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') : $ipAddress);
        // return $statistic->save();
        throw new \Exception('Depreciated storeAction referenced in Statistic.php');
    }
}
