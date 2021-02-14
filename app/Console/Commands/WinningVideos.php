<?php

namespace App\Console\Commands;

use App\Experiment\VideoExperiment;
use App\Experiment\VideoExperimentWinner;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Http\Controllers\Api\Traits\ExperimentTrait;

class WinningVideos extends Command
{
    use ExperimentTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'winning:videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chooses winning video from video experiments.';

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
        $experiments = VideoExperiment::where('active', 1)->get();
        foreach ($experiments as $ex) {
            $today = Carbon::parse(Carbon::now());
            $created_at = Carbon::parse($ex->created_at);
            $duration = $ex->duration;
            $videoIds = [];
            $goals = $ex->goals ? json_decode($ex->goals) : [];
            $endDate = $created_at->addDays($duration);
            $completed = ['active' => 0, 'end_date' => Carbon::now()];
            if ($today->gt($endDate) || $today->equalTo($endDate)) {
                if ($ex->action == 1) {
                    $results = $this->videoStats($ex);
                    $videoIds = array_keys($results);
                    $videoIdA = $videoIds[0];
                    $videoIdB = $videoIds[1];
                    $data = [$videoIdA => 0, $videoIdB => 0];
                    if (count($videoIds)) {
                        foreach ($goals as $goal) {
                            if ($goal == 'engagement') $goal = 'avg_engagement';
                            if ($goal == 'link_clicks') $goal = 'clicks';

                            if ($results[$videoIdA][$goal]) $data[$videoIdA] += 1;
                            if ($results[$videoIdB][$goal]) $data[$videoIdB] += 1;
                        }
                        $store = [];
                        if ($data[$videoIdA] != $data[$videoIdB])
                            if ($data[$videoIdA] > $data[$videoIdB])
                                $store = ['video_id' => $videoIdB, 'winner_id' => $videoIdA];
                            elseif ($data[$videoIds[0]] < $data[$videoIds[1]])
                                $store = ['video_id' => $videoIdA, 'winner_id' => $videoIdB];
                        if (count($store)) {
                            $store['video_experiment_id'] = $ex->id;
                            VideoExperimentWinner::create($store);
                        }
                        $ex->update($completed);
                    }
                } elseif ($ex->action == 2) {
                    $ex->update($completed);
                }
            }
        }
    }
}
