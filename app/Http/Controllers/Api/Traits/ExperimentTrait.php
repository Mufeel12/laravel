<?php
namespace App\Http\Controllers\Api\Traits;

use App\Statistic;
use App\Video;
use Carbon\Carbon;

trait ExperimentTrait {

    public $collect = [
        'type'          => 0,
        'play_rate'     => 0,
        'views'         => 0,
        'impressions'   => 0,
        'url'           => 0,
        'clicks'        => 0,
        'email_capture' => 0,
        'watch_time'    => 0,
        'watch_average' => 0,
        'avg_duration'  => 0,
        'avg_engagement'=> 0
    ];

    public function winning_thumbnail($ex)
    {
        $first_thumbnail = $ex->clickCounts->first();
        $second_thumbnail = $ex->clickCounts->last();

        $first_stats = $this->calculate_stats($first_thumbnail);
        $second_stats = $this->calculate_stats($second_thumbnail);
        
        if ($first_stats['play_rate'] > $second_stats['play_rate'])
            return [
                'a' => ['status' => true, 'statistics'   => $first_stats],
                'b' => ['status' => false, 'statistics'  => $second_stats]
            ];
        elseif ($first_stats['play_rate'] < $second_stats['play_rate']) 
            return [
                'a' => ['status' => false, 'statistics'   => $first_stats],
                'b' => ['status' => true, 'statistics'    => $second_stats]
            ];
        else 
            return [
                'a' => ['status' => false, 'statistics'   => $first_stats],
                'b' => ['status' => false, 'statistics'    => $second_stats]
            ];
        
    }

    public function calculate_stats($thumbnail)
    {
        if (!$thumbnail) {
            return response()->json(['success' => 0]);
        }
        $statistics = Statistic::where([
            'experiment_id' => $thumbnail->experiment_id,
            'experiment_click_id' => $thumbnail->id
        ])->get();
        $impressions = 0;
        $views = 0;
        $playRate = 0;
        $clicks = 0;
        $email_capture = 0;
        $watch_start = 0;
        $watch_end = 0;
        foreach ($statistics as $stat) {
            if ($stat->event == 'impression') $impressions = $impressions + 1;
            // if ($stat->event == 'video_view') $views = $views + 1;
            if ($stat->event == 'click') $clicks = $clicks + 1;
            if ($stat->event == 'email_capture') $email_capture = $email_capture + 1;
            if ($stat->watch_start != 0) $watch_start = $watch_start + $stat->watch_start;
            if ($stat->watch_end != 0) $watch_end = $watch_end + $stat->watch_end;
        }
        $views = Statistic::where(['experiment_id' => $thumbnail->experiment_id,'experiment_click_id'  => $thumbnail->id, 'event' => 'video_view'])->groupBy('watch_session_id')->get()->count();
        $playRate   = $views && $impressions ? $views * 100 / $impressions : 0;
        $watchtime  = abs($watch_end - $watch_start);

        return [
            'type'          => $thumbnail->type,
            'play_rate'     => number_format($playRate, 2),
            'views'         => $views,
            'impressions'   => $impressions,
            'url'           => $thumbnail->url,
            'clicks'        => $clicks,
            'email_capture' => $email_capture,
            'watch_time'    => gmdate('i', $watchtime),
            'watch_average' => $watchtime && $views ? ($watchtime / 60) / $views : 0
        ];
    }

    // Video Experimentts
    public function videoStats($ex)
    {
        $videoIds = [$ex->video_id_a, $ex->video_id_b];
        $data = [$ex->video_id_a => $this->collect, $ex->video_id_b => $this->collect];

        foreach ($videoIds as $id) {

            $stats = Statistic::where(['video_experiment_id' => $ex->id,'video_id'  => $id])->get();
            $totalWatchStart = 0;
            $totalWatchEnd = 0;
            // $unique_ref = null;
            foreach ($stats as $stat) {
                if ($stat->event == 'impression') $data[$id]['impressions'] += 1;
                if ($stat->event == 'video_view') {
                    // if (!$unique_ref || $unique_ref != $stat->watch_session_id) {
                    //     $data[$id]['views'] += 1;
                    // }
                    if ($stat->watch_start) $totalWatchStart += $stat->watch_start;
                    if ($stat->watch_end) $totalWatchEnd += $stat->watch_end;
                    // $unique_ref = $stat->watch_session_id;
                };
                if ($stat->event == 'click') $data[$id]['clicks'] += 1;
                if ($stat->event == 'email_capture') $data[$id]['email_capture'] += 1;
            }
            $data[$id]['views'] = Statistic::where(['video_experiment_id' => $ex->id,'video_id'  => $id, 'event' => 'video_view'])->groupBy('watch_session_id')->get()->count();
            $totalDuration = Video::find($id)->duration * $data[$id]['views'];
            $totalWatched  = $totalWatchEnd - $totalWatchStart;
            $data[$id]['play_rate'] = $data[$id]['views'] && $data[$id]['impressions'] ? number_format($data[$id]['views'] * 100 / $data[$id]['impressions'], 2) : 0;

            $data[$id]['avg_engagement'] = $totalWatched && $totalDuration ? number_format($totalWatched * 100 / $totalDuration, 2) : 0;
            $data[$id]['avg_duration'] = $totalWatched && $data[$id]['views'] ? gmdate('i:s', abs($totalWatched / $data[$id]['views'])) : 0;
            $data[$id]['video_id'] = $id;
        }
        return $data;
    }

}