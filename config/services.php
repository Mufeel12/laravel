<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'authy' => [
        'secret' => env('AUTHY_SECRET'),
    ],
    'site_domain' => [
        'url' => env('ROOT_URL'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],
    'mandrill' => [
        'secret' => env('MANDRILL_SECRET')
    ],
    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model'  => App\User::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'sparkpost' => [
        'secret' => '1ec929e74b0e7a2642e3f0f4d76aaaefa67cd191',
    ],

    'braintree' => [
        'environment'   => env('BRAINTREE_ENV'),
        'merchantId'    => env('BRAINTREE_MERCHANT_ID'),
        'publicKey'     => env('BRAINTREE_PUBLIC_KEY'),
        'privateKey'    => env('BRAINTREE_PRIVATE_KEY')
    ],

    'passport' => [
        'login_end_point'        => env('PASSPORT_LOGIN_ENDPOINT'),
        'login_client_end_point' => env('PASSPORT_CLIENT_ENDPOINT'),
        'passport_client_id'     => env('PASSPORT_CLIENT_ID'),
        'passport_client_secret' => env('PASSPORT_CLIENT_SECRET'),
        'expires_hours'          => 8,
        'expires_remember_me'    => 60,
    ],

    'subscription' => [
        'trial_duration' => 7
    ],

    'full_contact_api' => [
        'key' => env('FULL_CONTACT_API_KEY'),
        'url' => env('FULL_CONTACT_API_URL', 'https://api.fullcontact.com/v3/person.enrich')
    ],

    'integrations' => [
        'aweber'       => [
            'client_id'     => env('AWEBER_API_KEY'),
            'client_secret' => env('AWEBER_SECRET'),
        ],
        'mail_chimp'   => [
            'client_id'     => env('MAILCHIMP_CLIENT_ID'),
            'client_secret' => env('MAILCHIMP_CLIENT_SECRET'),
            'api_key' => env('MAILCHIMP_API_KEY'),
            'server_prefix' => env('MAILCHIMP_SERVER_PREFIX'),
        ],
        'active_campaign'   => [
            'api_key' => env('ACTIVECAMPAIGN_API_KEY'),
        ],
        'get_response' => [
            'client_id'     => env('GETRESPONSE_CLIENT_ID'),
            'client_secret' => env('GETRESPONSE_CLIENT_SECRET'),
        ],
        'gotowebinar'  => [
            'consumer_key' => env('GOTOWEBINAR_KEY'),
            'secret_key'   => env('GOTOWEBINAR_SECRET')
        ],
        'zapier'       => [
            'client_id'     => env('ZAPIER_KEY'),
            'client_secret' => env('ZAPIER_CLIENT_SECRET'),
        ],
        'zoom'         => [
            'client_id'     => env('ZOOM_CLIENT_ID'),
            'client_secret' => env('ZOOM_CLIENT_SECRET'),
        ],
        'zoho'         => [
            'client_id'     => env('ZOHO_CLIENT_ID'),
            'client_secret' => env('ZOHO_CLIENT_SECRET'),
        ]
        ],
        '__adilo' => [
            'ROOT_URL' => env('ROOT_URL')
        ],
];
