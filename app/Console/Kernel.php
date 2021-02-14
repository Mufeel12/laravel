<?php

namespace App\Console;

use App\Console\Commands\BunnyCdnExportLogsCron;
use App\Console\Commands\StoreVideoSessionCount;
use App\Console\Commands\UserStatusUpdate;
use App\Console\Commands\SendNotifyByEmailCron;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\OverageBandwidthCron;
use App\Console\Commands\ChangePayPalSubscriptionCron;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\FireVideoUpload',
        BunnyCdnExportLogsCron::class,
        UserStatusUpdate::class,
        OverageBandwidthCron::class,
        SendNotifyByEmailCron::class,
        ChangePayPalSubscriptionCron::class,
        StoreVideoSessionCount::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule
            ->command('bunny-cdn-export:cron')
            ->everyFiveMinutes();
        $schedule
            ->command('user-status:update')
            ->everyMinute();
        $schedule
            ->command('overage-bandwidth:update')
            ->daily();
        $schedule
            ->command('send-notify:start')
            ->daily();
        $schedule
            ->command('subscription:change')
            ->daily();
        $schedule
            ->command('winning:thumbnails')
            ->everyMinute();
        $schedule
            ->command('winning:videos')
            ->everyMinute();
        $schedule
            ->command('overusage:cron')
            ->everyFiveMinutes();
//        $schedule
//            ->command('StoreVideoSessionCount')
//            ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
