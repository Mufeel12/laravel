<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $table = 'integrations';

    protected $fillable = [
        'user_id', 'display_name', 'service_name', 'service_key', 'api_key', 'hash_key', 'service_url',
        'access_token', 'refresh_token', 'account_id', 'metadata', 'refresh_flag'
    ];

    CONST SERVICE_KEYS = [
        'aweber', 'mailchimp', 'getresponse', 'keap', 'convertkit', 'activecampaign', 'gotowebinar', 'zoom', 'webinarjam',
        'zoho', /*'salesforce', 'pardot', 'marketo', */
        'hubspot', 'facebook', 'twitter', 'youtube', 'linkedin', 'zapier'
    ];

    CONST SERVICE_TYPE = [
        'email'        => ['aweber', 'mailchimp', 'getresponse', 'keap', 'convertkit', 'activecampaign'],
        'webinar'      => ['gotowebinar', 'zoom', 'webinarjam'],
        'crm'          => ['zoho', /*'salesforce', 'pardot', 'marketo', */
                           'hubspot'],
        'social_media' => ['facebook', 'twitter', 'youtube', 'linkedin'],
        'other'        => ['zapier'],
    ];

    CONST SERVICE_LIST = [
        'aweber'         => [
            'controller'   => 'Aweber',
            'api_url'      => 'https://api.aweber.com/1.0/',
            'auth_url'     => 'https://auth.aweber.com/oauth2/authorize',
            'token_url'    => 'https://auth.aweber.com/oauth2/token',
            'redirect_uri' => '/api/oauth/aweber'
        ],
        'mailchimp'      => [
            'controller'   => 'MailChimp',
            'api_url'      => 'https://us7.api.mailchimp.com/3.0/',
            'auth_url'     => 'https://login.mailchimp.com/oauth2/authorize',
            'token_url'    => 'https://login.mailchimp.com/oauth2/token',
            'redirect_uri' => '/api/oauth/mailchimp'
        ],
        'getresponse'    => [
            'controller'   => 'GetResponse',
            'api_url'      => 'https://api.getresponse.com/v3/',
            'auth_url'     => 'https://app.getresponse.com/oauth2_authorize.html',
            'token_url'    => 'https://api.getresponse.com/v3/token',
            'redirect_uri' => '/api/oauth/getresponse'
        ],
        'keap'           => [
            'controller'   => 'Keap',
            'redirect_uri' => '/api/oauth/keap'
        ],
        'convertkit'     => [
            'controller' => 'ConvertKit',
            'api_url'    => 'https://api.convertkit.com/v3/'
        ],
        'activecampaign' => [
            'controller' => 'ActiveCampaign',
            'api_url'    => 'https://account.api-us1.com'
        ],
        'gotowebinar'    => [
            'controller'   => 'GoToWebinar',
            'api_url'      => '',
            'auth_url'     => 'https://api.getgo.com/oauth/v2/authorize',
            'token_url'    => 'https://api.getgo.com/oauth/v2/token',
            'redirect_uri' => '/api/oauth/gotowebinar',
        ],
        'zoom'           => [
            'controller'   => 'Zoom',
            'api_url'      => 'https://api.zoom.us/v2/',
            'auth_url'     => 'https://zoom.us/oauth/authorize',
            'token_url'    => 'https://zoom.us/oauth/token',
            'redirect_uri' => '/api/oauth/zoom'
        ],
        'webinarjam'     => [
            'controller' => 'WebinarJam',
            'api_url'    => 'https://app.webinarjam.com/api/v2/'
        ],
        'zoho'           => [
            'controller'   => 'ZohoCRM',
            'api_url'      => 'https://www.zohoapis.com/crm/v2/',
            'auth_url'     => 'https://accounts.zoho.com/oauth/v2/auth',
            'token_url'    => 'https://accounts.zoho.com/oauth/v2/token',
            'redirect_uri' => '/api/oauth/zoho'
        ],
        //        'salesforce'     => ['controller' => 'SalesforceCRM', 'api_url' => ''],
        //        'pardot'         => ['controller' => 'Pardot', 'api_url' => ''],
        //        'marketo'        => ['controller' => 'Marketo', 'api_url' => ''],
        'hubspot'        => [
            'controller' => 'HubSpot',
            'api_url'    => 'https://app.hubspot.com/'
        ],
        'facebook'       => [
            'controller' => 'Facebook',
            'api_url'    => ''
        ],
        'twitter'        => [
            'controller' => 'Twitter',
            'api_url'    => ''
        ],
        'youtube'        => [
            'controller' => 'Youtube',
            'api_url'    => ''
        ],
        'linkedin'       => [
            'controller' => 'LinkedIn',
            'api_url'    => ''
        ],
        'zapier'         => [
            'controller' => 'Zapier',
            'api_url'       => '',
            'auth_url'      => '',
            'token_url'     => '',
            'redirect_uri'  => '/'
        ]
    ];

    /**
     * Get Controller name of services
     *
     * @param null $service_key
     * @return array|bool|mixed
     */
    public static function getServiceController($service_key = null)
    {
        if (!is_null($service_key)) {
            $service_key = strtolower($service_key);
            if (isset(self::SERVICE_LIST[$service_key])) {
                $controller = 'App\Http\Controllers\Integrations\\' . self::SERVICE_LIST[$service_key]['controller'];
                if (class_exists($controller)) {
                    $class = new $controller();

                    if (isset($class)) {
                        return $class;
                    }
                }
            }

            return false;
        }

        $service_lists = collect(self::SERVICE_LIST);

        return $service_lists->map(function ($el) {
            self::getServiceController($el);
        });
    }

    public function getCreatedAtAttribute($value) {
        return time_according_to_user_time_zone($value);
    }


    public function getUpdatedAtAttribute($value) {
        return time_according_to_user_time_zone($value);
    }


    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function lists()
    {
        return $this->hasOne('App\IntegrationList', 'integration_id');
    }
}
