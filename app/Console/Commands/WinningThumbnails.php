<?php

namespace App\Console\Commands;

use App\Experiment\ThumbnailExperiment;
use App\Video;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Http\Controllers\Api\Traits\ExperimentTrait;

class WinningThumbnails extends Command
{
    use ExperimentTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winning:thumbnails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chooses winning thumbnail from thumbnail experiments.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $experiments = ThumbnailExperiment::where('active', 1)->get();
        foreach ($experiments as $ex) {
            $today = Carbon::parse(Carbon::now());
            $created_at = Carbon::parse($ex->created_at);
            $duration = $ex->duration;
            $url = false;
            $video = false;
            $endDate = $created_at->addDays($duration);
            $completed = ['active' => 0, 'end_date' => Carbon::now()];
            if ($today->gt($endDate) || $today->equalTo($endDate)) {
                if ($ex->action == 1) {

                    $check = $this->winning_thumbnail($ex);
                    if ($check['a']['status'])      $url = $check['a']['statistics']['url'];
                    elseif ($check['b']['status'])  $url = $check['b']['statistics']['url'];
                    $video = Video::find($ex->video_id);
                    $video->update(['thumbnail' => $url]);

                    $ex->update($completed);
                } elseif ($ex->action == 2) {
                    $ex->update($completed);
                }
            }
        }
    }
}
