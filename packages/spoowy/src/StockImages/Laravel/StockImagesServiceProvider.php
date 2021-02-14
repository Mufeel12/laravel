<?php namespace Spoowy\StockImages\Laravel;

use Spoowy\StockImages\StockImages;
use Illuminate\Support\ServiceProvider;

/**
 * Class ImageServiceProvider
 * @package App\Providers
 *
 */
class StockImagesServiceProvider extends ServiceProvider
{

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/stockimages.php' => config_path('stockimages.php'),
        ]);
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('stockimages', function ($app) {
            $config = $app['config']->get('stockimages');
            return new StockImages($config);
        });

    }

}
