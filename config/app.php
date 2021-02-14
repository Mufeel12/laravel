<?php

return [

    'suspendPeriod' => env('SUSPEND_PERIOD', 7),
    'permitted_chars' => '0123456789!@#$%^&*()abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',

    'mail_from' => [
        'address' => 'accounts@bigcommand.com',
        'name' => 'Bigcommand Accounts',
        'subject' => 'Confirm Your Adilo Account - Verify Your Email.',
    ],

    'mail_subjects' => [
        'subscription_renewed' => '[BigCommand] Your subscription renewal success',
        'subscription_failed' => '[BigCommand] ACTION REQUIRED: Your subscription renewal failed',
        'account_suspended' => '[BigCommand] ACTION REQUIRED: Your account is about to be suspended',
        'account_suspended_final' => '[BigCommand] Your account has been suspended for non-payment',
        'invoice' => '[BigCommand] Receipt',
        'estimate' => '[BigCommand] Estimate'
    ],

    'cycle_options' => [
        'today'     => [ 'value' => 'today',     'name' => 'Today (Now)' ],
        'end_cycle' => [ 'value' => 'end_cycle', 'name' => 'End of current cycle' ],
        'on_date'   => [ 'value' => 'on_date',   'name' => 'On a specific date' ],
    ],

    'payment_options' => [
        'on_log_in' => [ 'value' => 'on_log_in', 'name' => 'User is prompted to pay on log in' ],
        'manual'    => [ 'value' => 'manual',    'name' => 'Manually process payment' ],
        'waive'     => [ 'value' => 'waive',     'name' => 'Waive payment adjustment' ],
    ],


    'status_plan' => 'Active,Inactive,Trial,Expired,Cancelled,Failed,VerifyRequired',
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'MotionCTA'),

    "notification" => [
        'regard' => 'Bigcommand LLC',
        'address' => "108 West 13th Street,",
        'address_1' => 'Wilmington, DE ',
        'address_2' => '19801',
        'support_text' => 'Contact support',
        'support_link' => 'https://help.bigcommand.com',
    ],
    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),
    'root_url' => env('ROOT_URL', 'http://localhost:8000'),
    'site_url' => env('ROOT_URL'),
    'site_domain' => env('SITE_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),
    'receipt_mask' => 'BC-',
    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => env('APP_LOG', 'single'),

    'log_level' => env('APP_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        Laravel\Spark\Providers\SparkServiceProvider::class,
        App\Providers\SparkServiceProvider::class,
        Laravel\Cashier\CashierServiceProvider::class,

        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class,
        Spatie\SearchIndex\SearchIndexServiceProvider::class,
        Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,
        Barryvdh\Debugbar\ServiceProvider::class,
        //Barryvdh\Cors\ServiceProvider::class,
        // Spatie\Browsershot\Browsershot::class,
        #'GrahamCampbell\Flysystem\FlysystemServiceProvider',
        Pixelpeter\Woocommerce\WoocommerceServiceProvider::class,
        ProbablyRational\Wasabi\WasabiServiceProvider::class,
        Aws\Laravel\AwsServiceProvider::class,

        Torann\GeoIP\GeoIPServiceProvider::class,
        Soumen\Agent\AgentServiceProvider::class,

        Intervention\Image\ImageServiceProvider::class,
        Barryvdh\DomPDF\ServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App'            => Illuminate\Support\Facades\App::class,
        'Artisan'        => Illuminate\Support\Facades\Artisan::class,
        'Auth'           => Illuminate\Support\Facades\Auth::class,
        'Blade'          => Illuminate\Support\Facades\Blade::class,
        'Broadcast'      => Illuminate\Support\Facades\Broadcast::class,
        'Bus'            => Illuminate\Support\Facades\Bus::class,
        'Cache'          => Illuminate\Support\Facades\Cache::class,
        'Config'         => Illuminate\Support\Facades\Config::class,
        'Cookie'         => Illuminate\Support\Facades\Cookie::class,
        'Crypt'          => Illuminate\Support\Facades\Crypt::class,
        'DB'             => Illuminate\Support\Facades\DB::class,
        'Eloquent'       => Illuminate\Database\Eloquent\Model::class,
        'Event'          => Illuminate\Support\Facades\Event::class,
        'File'           => Illuminate\Support\Facades\File::class,
        'Gate'           => Illuminate\Support\Facades\Gate::class,
        'Hash'           => Illuminate\Support\Facades\Hash::class,
        'Lang'           => Illuminate\Support\Facades\Lang::class,
        'Log'            => Illuminate\Support\Facades\Log::class,
        'Mail'           => Illuminate\Support\Facades\Mail::class,
        'Notification'   => Illuminate\Support\Facades\Notification::class,
        'Password'       => Illuminate\Support\Facades\Password::class,
        'Queue'          => Illuminate\Support\Facades\Queue::class,
        'Redirect'       => Illuminate\Support\Facades\Redirect::class,
        'Redis'          => Illuminate\Support\Facades\Redis::class,
        'Request'        => Illuminate\Support\Facades\Request::class,
        'Response'       => Illuminate\Support\Facades\Response::class,
        'Route'          => Illuminate\Support\Facades\Route::class,
        'Schema'         => Illuminate\Support\Facades\Schema::class,
        'Session'        => Illuminate\Support\Facades\Session::class,
        'Storage'        => Illuminate\Support\Facades\Storage::class,
        'URL'            => Illuminate\Support\Facades\URL::class,
        'Validator'      => Illuminate\Support\Facades\Validator::class,
        'View'           => Illuminate\Support\Facades\View::class,
        'Form'           => 'Collective\Html\FormFacade',
        'Html'           => 'Collective\Html\HtmlFacade',
        'AWS'            => Aws\Laravel\AwsFacade::class,
        'OAuth'          => 'Artdarek\OAuth\Facade\OAuth',
        'StockImages'    => 'Spoowy\StockImages\Laravel\Facades\StockImages',
        'Spotlight'      => 'Spoowy\SpotlightSearch\SpotlightFacade',
        'Vimeo'          => 'Vinkla\Vimeo\Facades\Vimeo',
        'Youtube'        => Madcoda\Youtube\Facades\Youtube::class,
        'VideoAntHelper' => 'Spoowy\VideoAntHelper\VideoAntHelperFacade',
        'SearchIndex'    => Spatie\SearchIndex\SearchIndexFacade::class,
        'Woocommerce'    => Pixelpeter\Woocommerce\Facades\Woocommerce::class,

        'GeoIP'          => \Torann\GeoIP\Facades\GeoIP::class,
        'Agent'          => Soumen\Agent\Facades\Agent::class,
        'PDF'            => Barryvdh\DomPDF\Facade::class,

        'Integrations'   => App\Facades\Integrations::class,
        'ImageOptimizer' => Intervention\Image\ImageManagerStatic::class,
    ],

];
