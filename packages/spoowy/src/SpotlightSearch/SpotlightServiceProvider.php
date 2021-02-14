<?php namespace Spoowy\SpotlightSearch;

use Illuminate\Support\ServiceProvider;

/**
 * Class SpotlightServiceProvider
 * @package Spoowy\SpotlightSearch
 */
class SpotlightServiceProvider extends ServiceProvider
{
    /**
     * Register the Spotlight service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('spotlight', function($app) {
            return new Spotlight();
        });
    }
}