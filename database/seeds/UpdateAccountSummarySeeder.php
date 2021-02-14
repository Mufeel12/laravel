<?php

use Illuminate\Database\Seeder;

class UpdateAccountSummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usersId = DB::table('users')->pluck('id')->toArray();
        array_map(function ($userId) {
            $queryVideo = DB::table('videos')->where('owner', $userId);
            $videos_videoId = $queryVideo->pluck('video_id')->toArray();
            $videosId = $queryVideo->pluck('id')->toArray();
            $videos_count = $queryVideo->count();
            $viewsAllTime = DB::table('statistics')
                ->whereRaw("statistics.user_id = $userId AND statistics.event = 'video_view'")
                ->groupBy('watch_session_id')
                ->pluck('watch_session_id')
                ->toArray();
            $watchTime = DB::table('statistics')
                ->whereRaw("statistics.user_id = $userId AND statistics.event = 'video_view'")
                ->select(DB::raw('sum(statistics.watch_end - statistics.watch_start) as time'))
                ->pluck('time')
                ->toArray();

            $user_videos = $queryVideo->get();
            $user_videos->map(function ($video) {
                $views = DB::table('statistics')
                    ->where('video_id', $video->id)
                    ->where('event', 'video_view')
                    ->groupBy('watch_session_id')
                    ->count();
                DB::table('videos')
                    ->where('id', $video->id)
                    ->update(['views' => $views]);
            }, $user_videos);

            $projectsCount = DB::table('projects')->where('owner', $userId)->count();
            $contactCount = DB::table('subscribers')->where('user_id', $userId)->count();
            $bytesSentAllTime = DB::table('bunnycdn_bandwidth_records')->whereIn('video_id', $videos_videoId)->pluck('bytes_sent')->toArray();
            $subscriptionEnds = DB::table('subscriptions')->where('user_id', $userId)->pluck('ends_at')->first();
            $complianceCount = DB::table('compliance_records')->where('user_id', $userId)->count();
            $subscriptionStart = date('Y-m-d H:i:s', strtotime('-1 months', strtotime($subscriptionEnds)));
            $currentCycleBytesSent = DB::table('bunnycdn_bandwidth_records')->where('created_at', '>', $subscriptionStart)->whereIn('video_id', $videosId)->pluck('bytes_sent')->toArray();
            \App\AccountSummary::updateOrCreate(
                ['user_id' => $userId],
                [
                    'bandwidth_usage' => array_sum($currentCycleBytesSent),
                    'videos_views' => count($viewsAllTime),
                    'projects_count' => $projectsCount,
                    'videos_count' => $videos_count,
                    'contact_size' => $contactCount,
                    'views_total_watch_time' => array_sum($watchTime),
                    'bandwidth_all_time' => array_sum($bytesSentAllTime) / 1024 / 1024 / 1024,
                    'compliance' => $complianceCount,
                    'cost_this_month' => 0,
                    'cost_last_month' => 0,
                ]
            );
        }, $usersId);
    }
}
