<?php


namespace App\Repositories\Stage;


use App\Repositories\AbstractRepository;
use App\Stage;
use App\User;
use App\UserSettings;
use Illuminate\Support\Facades\DB;

class StageRepository extends AbstractRepository
{
    protected $model;

    public function __construct(Stage $stage)
    {
        parent::__construct($stage);
    }

    public function model()
    {
        return app(Stage::class);
    }

    /**
     * get video views count
     *
     * @param $video_id
     * @return mixed
     */
    private function getVideosViewCounts($video_id)
    {
        $counts = DB::table('statistics')->where('video_id', $video_id)
            ->where('event', 'video_view')
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0')
            ->groupBy('watch_session_id')
            ->groupBy('video_id')->get()->toArray();

        return sizeof($counts);
    }

    /**
     * get stage videos common query
     * @param $user
     * @return mixed
     */
    private function getCommonQuery($user)
    {
        if (is_null($user)){
            if (isset($stage_id) && !is_null($stage_id)){
                $user_id = UserSettings::where('stage_public_url', 'LIKE', '%'. $stage_id .'%')->pluck('user_id')->first();
                if (!is_null($user_id) && $user_id != '') {
                    $user_row = User::find($user_id);
                    $user = User::getUserDetails($user_row);
                }
            }
        }

        return DB::table('videos')
            ->select('videos.id')
            ->leftJoin('statistics', 'videos.id', '=', 'statistics.video_id')
            ->where('videos.team', $user->currentTeam()->id)
            ->where('videos.published_on_stage', 'true')
            ->groupBy('videos.id');
    }

    /**
     * Get query execute data
     *
     * @param $sel_query
     * @param bool $take_value
     * @param bool $paginate
     * @param int $paginate_value
     * @return mixed
     */
    private function getEndCommonQuery($sel_query, $take_value = false, $paginate = false, $paginate_value = 12)
    {
        if ($take_value) {
            $sel_query = $sel_query->take(10);
        }

        if ($paginate) {
            $sel_query = $sel_query->paginate($paginate_value)->getCollection()->transform(function ($value) {
                $value->view_count = $this->getVideosViewCounts($value->id);

                return $value;
            });
        } else {
            $sel_query = $sel_query->get()->map(function ($value) {
                $value->view_count = $this->getVideosViewCounts($value->id);

                return $value;
            });
        }

        return $sel_query;
    }

    /**
     * Get Popular Videos
     *
     * @param $user
     * @param bool $take_value
     * @param bool $paginate
     * @param int $paginate_value
     * @return mixed
     */
    public function getPopularVideosList($user, $take_value = false, $paginate = false, $paginate_value = 12)
    {
        $sel_query = $this->getCommonQuery($user)
            ->orderBy(DB::raw('COUNT(statistics.video_id)'), 'desc')
            ->orderBy('videos.created_at', 'desc');

        return $this->getEndCommonQuery($sel_query, $take_value, $paginate, $paginate_value);
    }

    /**
     * Get Featured Videos
     *
     * @param $user
     * @param bool $take_value
     * @param bool $paginate
     * @param int $paginate_value
     * @return mixed
     */
    public function getFeaturedVideosList($user, $take_value = false, $paginate = false, $paginate_value = 12)
    {
        $sel_query = $this->getCommonQuery($user)
            ->where('videos.featured_on_stage', 'true')
            ->orderBy(DB::raw('COUNT(statistics.video_id)'), 'desc')
            ->orderBy('videos.created_at', 'desc');

        return $this->getEndCommonQuery($sel_query, $take_value, $paginate, $paginate_value);
    }

    /**
     * get recent videos
     * @param $user
     * @param bool $take_value
     * @param bool $paginate
     * @param int $paginate_value
     * @return mixed
     */
    public function getRecentVideosList($user, $take_value = false, $paginate = false, $paginate_value = 12)
    {
        $sel_query = $this->getCommonQuery($user)
            ->orderBy('videos.created_at', 'desc')
            ->orderBy(DB::raw('COUNT(statistics.video_id)'), 'desc');

        return $this->getEndCommonQuery($sel_query, $take_value, $paginate, $paginate_value);
    }

    /**
     * Get Video data by video_id
     * @param $video_id
     * @return mixed
     */
    public function getVideoData($video_id)
    {
        return DB::table('videos')
            ->select(DB::raw('COUNT(statistics.video_id) AS `view_count`'), 'videos.id')
            ->leftJoin('statistics', 'videos.id', '=', 'statistics.video_id')
            ->where('statistics.event', 'video_view')
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0')
            ->groupBy('videos.id')
            ->where('videos.video_id', $video_id)
            ->first();
    }
    /**
     * Get Video data by video_id
     * @param $video_id
     * @return mixed
     */
    public function getPublicVideoData($video_id)
    {
        return DB::table('videos')
            ->select(DB::raw('COUNT(statistics.video_id) AS `view_count`'), 'videos.id')
            ->leftJoin('statistics', 'videos.id', '=', 'statistics.video_id')
            ->groupBy('videos.id')
            ->where('videos.video_id', $video_id)
            ->first();
    }
}
