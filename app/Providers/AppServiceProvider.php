<?php

namespace App\Providers;

use App\ElasticTranscoderJob;
use App\Observers\ElasticTranscoderJobObserver;
use App\Observers\ProjectObserver;
use App\Observers\SlateObserver;
use App\Observers\StatisticObserver;
use App\Observers\VideoFileRelevantThumbnailObserver;
use App\Observers\VideoObserver;
use App\Observers\VideoProcessingEventObserver;
use App\Observers\VideoQualityFileObserver;
use App\Project;
use App\Repositories\IntegrationRepository;
use App\Slate;
use App\Statistic;
use App\Video;
use App\VideoProcessingEvent;
use App\VideoQualityFile;
use App\VideoRelevantThumbnail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Project::observe(ProjectObserver::class);
        Video::observe(VideoObserver::class);
        VideoRelevantThumbnail::observe(VideoFileRelevantThumbnailObserver::class);
        ElasticTranscoderJob::observe(ElasticTranscoderJobObserver::class);
        VideoQualityFile::observe(VideoQualityFileObserver::class);
        Slate::observe(SlateObserver::class);
        Statistic::observe(StatisticObserver::class);
        VideoProcessingEvent::observe(VideoProcessingEventObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Integrations', function () {
            return new IntegrationRepository();
        });
    }
}
