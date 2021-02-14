<?php

namespace Spoowy\VideoAntHelper;

use Illuminate\Support\ServiceProvider;

class VideoAntHelperServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('videoanthelper', function($app) {
            return new VideoAntHelper();
        });
    }
}