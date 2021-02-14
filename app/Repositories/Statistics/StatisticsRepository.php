<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 13.11.2015
 * Time: 14:21.
 */

namespace App\Repositories\Statistics;

use App\Repositories\AbstractRepository;
use App\Statistic;
use App\Project;
use Illuminate\Support\Facades\DB;

/**
 * Class StatisticsRepository.
 */
class StatisticsRepository extends AbstractRepository
{
    protected $view_event = 'video_view';

    /**
     * @param Statistic $statistics
     */
    public function __construct(Statistic $statistics)
    {
        parent::__construct($statistics);
    }

    public function model()
    {
        return app(Statistic::class);
    }

    /**
     * @param $user
     * @return mixed
     */
    public function getResourceUrls($user)
    {
        $query = $this->model()
            ->where('statistics.domain', config('app.site_domain'))
            ->where('team_id', $user->currentTeam()->id)
            ->where('event', $this->view_event)
            ->groupBy('domain')
            ->pluck('domain');

        return $query;
    }
    function getProjectsIds($projectIds){
            return Project::whereIn('id',$projectIds)->select('id')->get()->pluck('id');

    }
    /**
     * @param $data
     * @param $user
     * @return mixed
     */
    private function makeCommonQuery($data, $user)
    {
        $query = $this->model()
            ->where('statistics.team_id', $user->currentTeam()->id)
            ->where('statistics.domain', config('app.site_domain'));
        if (isset($data['project_id'])) {
            
            $query = $query->where('statistics.project_id', $data['project_id']);
        } elseif (isset($data['checkedProjects'])) {
            $project_ids = $this->getProjectsIds($data['checkedProjects']);
            $query = $query->whereIn('statistics.project_id', $project_ids);
        }

        if (isset($data['video_id'])) {
            $query = $query->where('statistics.video_id', $data['video_id']);
        } elseif (isset($data['checkedVideos'])) {
            $query = $query->whereIn('statistics.video_id', $data['checkedVideos']);
        }

        if (isset($data['country_code'])) {
            $query = $query->where('statistics.country_code', $data['country_code']);
        } elseif (isset($data['checkedCountries'])) {
            $query = $query->whereIn('statistics.country_code', $data['checkedCountries']);
        }

        if (isset($data['domain'])) {
            $query = $query->where('statistics.domain', $data['domain']);
        } elseif (isset($data['checkedDomains'])) {
            $query = $query->whereIn('statistics.domain', $data['checkedDomains']);
        }

        if (isset($data['kind'])) {
            $query = $query->where('statistics.kind', $data['kind']);
        } elseif (isset($data['checkedDevices'])) {
            $query = $query->whereIn('statistics.kind', $data['checkedDevices']);
        }

        return $query;
    }

    /**
     * make views common query
     * @param $query
     * @param string $event
     * @return mixed
     */
    private function makeViewCommonQuery($query, $event = 'video_view')
    {
        if($event != 'video_view'){
            $query = $query
                ->where('statistics.domain', config('app.site_domain'))
                ->groupBy('statistics.watch_session_id')
                ->groupBy('statistics.video_id');
        }else{
            $query = $query
                ->where(function ($q) {
                    $q->where('statistics.watch_start', '<>', '0')
                        ->orWhere('statistics.watch_end', '<>', '0');
                })
                ->where('statistics.watch_end', '<>', '0')
                ->where('statistics.domain', config('app.site_domain'))
                ->groupBy('statistics.watch_session_id')
                ->groupBy('statistics.video_id');
        }

        if (is_array($event)) {
            $query = $query->whereIn('statistics.event', $event);
        } else {
            $query = $query->where('statistics.event', $event);
        }

        return $query;
    }

    /**
     * Reset XLabel of trend chart
     * @param $calc_data
     * @param $start_date
     * @param $end_date
     * @param $cal_number
     * @return mixed
     */
    private function calcXLabel($calc_data, $start_date, $end_date, $cal_number)
    {
        $calc_data['xLabel'] = [$start_date];
        for ($i = 0; $i < $cal_number; $i++) {
            $dStr = date('d-m-Y', strtotime("-" . $i . " day", strtotime($end_date)));
            $calc_data['xLabel'][] = $dStr;
        }

        $sortTime = [];
        foreach ($calc_data['xLabel'] as $key => $values) {
            $sortTime[] = strtotime($values);
        }
        asort($sortTime);

        $calc_data['xLabel'] = [];
        foreach ($sortTime as $value) {
            $calc_data['xLabel'][] = date('M d, Y', $value);
        }

        $calc_data['xLabel'] = array_unique($calc_data['xLabel']);

        return $calc_data;
    }

    /**
     * formatting engagement views data.
     * @param $date
     * @param $array_data
     * @return array
     */
    private function setTrendDataFormat($date, $array_data)
    {
        $format_ary = [];
        for ($i = 0; $i < count($array_data); $i++) {
            $format_ary[$i] = [];
        }

        if (sizeof($date) > 0) {
            foreach ($date as $key => $row) {
                for ($i = 0; $i < count($array_data); $i++) {
                    $format_ary[$i][$key] = 0;

                    if (array_key_exists($row, $array_data[$i])) {
                        $format_ary[$i][$key] = $array_data[$i][$row];
                    }
                }
            }
        }

        return $format_ary;
    }

    /**
     * Get date query
     *
     * @param $data
     * @param $user
     * @return array
     */
    private function getDateRangeData($data, $user)
    {
        if ($data['end_date'] == '' || is_null($data['end_date']) || $data['end_date'] == '0000-00-00') {
            $end_date = date('Y-m-d', strtotime(now($user->settings->timezone)));
        } else {
            $end_date = date('Y-m-d', strtotime($data['end_date']));
        }
        $start_date = date('Y-m-d', strtotime($data['start_date']));

        $diff = abs(strtotime($end_date) - strtotime($start_date));
        $days = floor($diff / (60 * 60 * 24));

        $prev_date = date('Y-m-d H:i:s', strtotime('-' . $days . ' days', strtotime($start_date)));

        $today = date('Y-m-d', strtotime(now($user->settings->timezone)));
        $yesterday = date('Y-m-d', strtotime('-1 days', strtotime($today)));

        return [$start_date, $end_date, $days, $prev_date, $today, $yesterday];
    }

    /**
     * Get Impressions data.
     *
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param $prev_date
     * @param $today
     * @param $yesterday
     * @return array
     */
    public function getImpressionsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday)
    {
        $q_ary = [
            'total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0,
        ];

        $total_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where('statistics.event', 'impression')
            ->groupBy('statistics.watch_session_id')
            ->groupBy('statistics.video_id')
            ->get()->toArray();
        $q_ary['total'] = sizeof($total_qry);

        $prev_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $start_date)
            ->where('statistics.created_at', '>', $prev_date)
            ->where('statistics.event', 'impression')
            ->groupBy('statistics.watch_session_id')
            ->groupBy('statistics.video_id')
            ->get()->toArray();
        $q_ary['prev'] = sizeof($prev_qry);

        $today_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $today)
            ->where('statistics.event', 'impression')
            ->groupBy('statistics.watch_session_id')
            ->groupBy('statistics.video_id')->get()->toArray();
        $q_ary['today'] = sizeof($today_qry);

        $yesterday_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $yesterday)
            ->where('statistics.event', 'impression')
            ->groupBy('statistics.watch_session_id')
            ->groupBy('statistics.video_id')
            ->get()->toArray();

        $q_ary['yesterday'] = sizeof($yesterday_qry);

        return $q_ary;
    }

    /**
     * Get Total views count
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param $prev_date
     * @param $today
     * @param $yesterday
     * @param $event
     * @return array
     */
    public function getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, $event = 'video_view')
    {
        $q_ary = [
            'total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0,
        ];
 
        $total_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date);

        $total_qry = $this->makeViewCommonQuery($total_qry, $event)
            ->get()->toArray();

        $q_ary['total'] = sizeof($total_qry);

        $prev_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $start_date)
            ->where('statistics.created_at', '>', $prev_date);
        $prev_qry = $this->makeViewCommonQuery($prev_qry, $event)
            ->get()->toArray();
        $q_ary['prev'] = sizeof($prev_qry);

        $today_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $today);
        $today_qry = $this->makeViewCommonQuery($today_qry, $event)
            ->get()->toArray();
        $q_ary['today'] = sizeof($today_qry);

        $yesterday_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $yesterday);
        $yesterday_qry = $this->makeViewCommonQuery($yesterday_qry, $event)
            ->get()->toArray();
        $q_ary['yesterday'] = sizeof($yesterday_qry);

        return $q_ary;
    }

    /**
     * get Total watched time
     *
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param $prev_date
     * @param $today
     * @param $yesterday
     * @return array
     */
    public function getTotalWatchTime($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday)
    {
        $q_ary = [
            'total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0,
        ];

        $total_start = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where('statistics.watch_end', '<>', '0')
            ->where('statistics.event', $this->view_event)
            ->sum('watch_end');
        $total_end = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where('statistics.watch_end', '<>', '0')
            ->where('statistics.event', $this->view_event)
            ->sum('watch_start');
        // $q_ary['total'] = abs($total_end - $total_start);
        $q_ary['total'] = abs($total_end - $total_start);


        $prev_end = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $start_date)
            ->where('statistics.created_at', '>', $prev_date)
            ->where('statistics.watch_end', '<>', '0')
            ->where('statistics.event', $this->view_event)
            ->sum('watch_end');
        $prev_start = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $start_date)
            ->where('statistics.created_at', '>', $prev_date)
            ->where('statistics.event', $this->view_event)
            ->where('statistics.watch_end', '<>', '0')
            ->sum('watch_start');
        $prevTotal = abs($prev_end - $prev_start);
        $q_ary['prev'] = $prevTotal;

        $watch_end = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $today)
            ->where('statistics.event', $this->view_event)
            ->where('statistics.watch_end', '<>', '0')
            ->sum('watch_end');
        $watch_start = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $today)
            ->where('statistics.event', $this->view_event)
            ->where('statistics.watch_end', '<>', '0')
            ->sum('watch_start');
        $watchTotal = abs($watch_end - $watch_start);
        $q_ary['today'] = $watchTotal;

        $yesterday_end = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $yesterday)
            ->where('statistics.event', $this->view_event)
            ->where('statistics.watch_end', '<>', '0')
            ->sum('watch_end');
        $yesterday_start = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $yesterday)
            ->where('statistics.event', $this->view_event)
            ->where('statistics.watch_end', '<>', '0')
            ->sum('watch_start');
        $yesterdayTotal = abs($yesterday_end - $yesterday_start);
        $q_ary['yesterday'] = $yesterdayTotal;

        return $q_ary;
    }

    /**
     * video length
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param $prev_date
     * @param $today
     * @param $yesterday
     * @return array
     */
    public function getTotalViewedVideosLength($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday)
    {
        $q_ary = [
            'total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0,
        ];

        $total_qry = $this->makeCommonQuery($data, $user)
            ->select('videos.duration')
            ->join('videos', 'statistics.video_id', '=', 'videos.id')
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date);
        $total_qry = $this->makeViewCommonQuery($total_qry, $this->view_event)
            ->pluck('videos.duration')
            ->toArray();

        $q_ary['total'] = array_sum($total_qry);

        $prev_qry = $this->makeCommonQuery($data, $user)
            ->select('videos.duration')
            ->join('videos', 'statistics.video_id', '=', 'videos.id')
            ->where(DB::raw('date(statistics.created_at)'), '<=', $start_date)
            ->where('statistics.created_at', '>', $prev_date);
        $prev_qry = $this->makeViewCommonQuery($prev_qry, $this->view_event)
            ->pluck('videos.duration')
            ->toArray();

        $q_ary['prev'] = array_sum($prev_qry);

        $today_qry = $this->makeCommonQuery($data, $user)
            ->select('videos.duration')
            ->join('videos', 'statistics.video_id', '=', 'videos.id')
            ->where(DB::raw('date(statistics.created_at)'), $today);
        $today_qry = $this->makeViewCommonQuery($today_qry, $this->view_event)
            ->pluck('videos.duration')
            ->toArray();
        $q_ary['today'] = array_sum($today_qry);

        $yesterday_qry = $this->makeCommonQuery($data, $user)
            ->select('videos.duration')
            ->join('videos', 'statistics.video_id', '=', 'videos.id')
            ->where(DB::raw('date(statistics.created_at)'), $yesterday);
        $yesterday_qry = $this->makeViewCommonQuery($yesterday_qry, $this->view_event)
            ->pluck('videos.duration')
            ->toArray();

        $q_ary['yesterday'] = array_sum($yesterday_qry);

        return $q_ary;
    }

    /**
     * Get watched devices count.
     *
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public function getViewedDeviceCount($data, $user, $start_date, $end_date)
    {
        $q_ary = [
            'desktop' => 0, 'mobile' => 0, 'tablet' => 0, 'total' => 0
        ];

        $desktop_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where('statistics.kind', 'Desktop');
        $desktop_qry = $this->makeViewCommonQuery($desktop_qry, $this->view_event)
            ->get()->toArray();
        $q_ary['desktop'] = sizeof($desktop_qry);

        $mobile_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where('statistics.kind', 'Mobile');
        $mobile_qry = $this->makeViewCommonQuery($mobile_qry, $this->view_event)
            ->get()->toArray();
        $q_ary['mobile'] = sizeof($mobile_qry);

        $tablet_qry = $this->makeCommonQuery($data, $user)->where('statistics.created_at', '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where('statistics.kind', 'Tablet')
            ->where('statistics.event', $this->view_event);
        $tablet_qry = $this->makeViewCommonQuery($tablet_qry, $this->view_event)
            ->get()->toArray();
        $q_ary['tablet'] = sizeof($tablet_qry);

        $q_ary['total'] = $q_ary['desktop'] + $q_ary['mobile'] + $q_ary['tablet'];

        return $q_ary;
    }

    /**
     * Top videos lists
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param $event
     * @param $skip
     * @return mixed
     */
    public function getTopVideosList($data, $user, $start_date, $end_date, $event = ['video_view'], $skip = 0)
    {
        $top_videos = $this->makeCommonQuery($data, $user)
            ->select('videos.id', 'videos.title', DB::raw('videos.video_id AS `video_key`'), 'videos.thumbnail', 'videos.duration')
            ->join('videos', 'statistics.video_id', '=', 'videos.id')
            ->whereIn('statistics.event', $event)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0') 
            ->groupBy('videos.id')
            ->orderBy(DB::raw('COUNT(statistics.video_id)'), 'desc')
            ->orderBy('videos.created_at', 'desc');
        if ($skip != 0) {
            $top_videos = $top_videos->take($skip);
        }
        $top_videos = $top_videos->get()->map(function ($el) use($event,$start_date,$end_date,$data,$user) {
            $obj = $el;
            $obj->show = false;
            $obj->view_count = $this->makeCommonQuery($data, $user)->whereIn('statistics.event', $event)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })->where('statistics.video_id',$obj->id)
            ->where('statistics.watch_end', '<>', '0')->groupBy('statistics.watch_session_id')->get()->count();
            return $obj;
        });

        return $top_videos;
    }

    /**
     * Get Total views count
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param $prev_date
     * @param $today
     * @param $yesterday
     * @param $event
     * @return array
     */
    public function getUniqueViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, $event = 'video_view')
    {
        $q_ary = [
            'total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0,
        ];

        $total_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0')
            ->where('statistics.event', $event)
            ->groupBy('statistics.unique_ref')
            ->get()->toArray();

        $q_ary['total'] = sizeof($total_qry);

        $prev_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $start_date)
            ->where('statistics.created_at', '>', $prev_date)
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0')
            ->where('statistics.event', $event)
            ->groupBy('statistics.unique_ref')
            ->get()->toArray();
        $q_ary['prev'] = sizeof($prev_qry);

        $today_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $today)
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0')
            ->where('statistics.event', $event)
            ->groupBy('statistics.unique_ref')
            ->get()->toArray();
        $q_ary['today'] = sizeof($today_qry);

        $yesterday_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), $yesterday)
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0')
            ->where('statistics.event', $event)
            ->groupBy('statistics.unique_ref')
            ->get()->toArray();
        $q_ary['yesterday'] = sizeof($yesterday_qry);

        return $q_ary;
    }

    /**
     * Get Total views count
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param $prev_date
     * @param $today
     * @param $yesterday
     * @param $event
     * @return array
     */
    public function getCapturedContacts($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, $event = 'email_capture')
    {
        $q_ary = [
            'total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0,
        ];

        $total_qry = $this->makeCommonQuery($data, $user)
            ->select('statistics.id')
            ->join('subscribers', 'statistics.unique_ref', '=', 'subscribers.user_agent')
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date);

        $total_qry = $this->makeViewCommonQuery($total_qry, $event)
            ->groupBy('subscribers.email')
            ->get()->toArray();

        $q_ary['total'] = sizeof($total_qry);

        $prev_qry = $this->makeCommonQuery($data, $user)
            ->select('statistics.id')
            ->join('subscribers', 'statistics.unique_ref', '=', 'subscribers.user_agent')
            ->where(DB::raw('date(statistics.created_at)'), '<=', $start_date)
            ->where('statistics.created_at', '>', $prev_date);
        $prev_qry = $this->makeViewCommonQuery($prev_qry, $event)
            ->groupBy('subscribers.email')
            ->get()->toArray();
        $q_ary['prev'] = sizeof($prev_qry);

        $today_qry = $this->makeCommonQuery($data, $user)
            ->select('statistics.id')
            ->join('subscribers', 'statistics.unique_ref', '=', 'subscribers.user_agent')
            ->where(DB::raw('date(statistics.created_at)'), $today);
        $today_qry = $this->makeViewCommonQuery($today_qry, $event)
            ->groupBy('subscribers.email')
            ->get()->toArray();
        $q_ary['today'] = sizeof($today_qry);

        $yesterday_qry = $this->makeCommonQuery($data, $user)
            ->select('statistics.id')
            ->join('subscribers', 'statistics.unique_ref', '=', 'subscribers.user_agent')
            ->where(DB::raw('date(statistics.created_at)'), $yesterday);
        $yesterday_qry = $this->makeViewCommonQuery($yesterday_qry, $event)
            ->groupBy('subscribers.email')
            ->get()->toArray();
        $q_ary['yesterday'] = sizeof($yesterday_qry);

        return $q_ary;
    }

    /**
     * @param $country
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param $group_by
     * @return int
     */
    private function getCountriesViewsCount($country, $data, $user, $start_date, $end_date, $group_by)
    {
        $top_countries = $this->makeCommonQuery($data, $user)
            ->where('statistics.domain', config('app.site_domain'))
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date);
        $top_countries = $this->makeViewCommonQuery($top_countries)
            ->where('statistics.domain', config('app.site_domain'))
            ->where($group_by, $country)
            ->get()->toArray();

        return sizeof($top_countries);
    }

    /**
     * @param $city
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @return int
     */
    private function getCitiesImpressionsCount($city, $data, $user, $start_date, $end_date)
    {
        $total_qry = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where('statistics.event', 'impression')
            ->where('city', $city)
            ->groupBy('statistics.watch_session_id')
            ->groupBy('statistics.video_id')
            ->get()->toArray();

        return sizeof($total_qry);
    }

    /**
     * @param $city
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @return int
     */
    private function getCitiesViewsCount($city, $data, $user, $start_date, $end_date)
    {
        $city_views = $this->makeCommonQuery($data, $user)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date);
        $city_views = $this->makeViewCommonQuery($city_views)
            ->where('city', $city)
            ->get()->toArray();

        return sizeof($city_views);
    }

    public function getViewsLocations($data, $user, $start_date, $end_date)
    {
        $query = $this->makeCommonQuery($data, $user)
            ->select('city', 'country_code', 'country_name', 'latitude', 'longitude')
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where('statistics.event', $this->view_event)
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0')
            ->groupBy('city')
            ->get()->map(function ($el) use ($data, $user, $start_date, $end_date) {
                $el->impression_count = $this->getCitiesImpressionsCount($el->city, $data, $user, $start_date, $end_date);
                $el->view_count = $this->getCitiesViewsCount($el->city, $data, $user, $start_date, $end_date);

                return $el;
            });

        return $query;
    }

    /**
     * Top countries
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param $group_by
     * @param $skip
     * @return mixed
     */
    public function getViewsTopCountries($data, $user, $start_date, $end_date, $group_by = 'country_name', $skip = 0)
    {
        $top_countries = $this->makeCommonQuery($data, $user)
            ->select($group_by)
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
            ->where('statistics.event', $this->view_event)
            ->where('statistics.domain', config('app.site_domain'))
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0')
            ->groupBy($group_by)
            ->orderBy(DB::raw('COUNT(statistics.video_id)'), 'desc');
        if ($skip != 0) {
            $top_countries = $top_countries->take($skip);
        }
        $top_countries = $top_countries->get()->map(function ($el) use ($data, $user, $start_date, $end_date, $group_by) {
            $el->view_count = $this->getCountriesViewsCount($el->$group_by, $data, $user, $start_date, $end_date, $group_by);
            $el->title = $el->$group_by;

            return $el;
        });

        return $top_countries;
    }

    /**
     * Get Hours chart data.
     * @param $data
     * @param $user
     * @param $curr_date
     * @param $video_id
     * @param $prev_date
     * @return array
     */
    public function getHoursChartData($data, $user, $curr_date, $video_id = null, $prev_date = '')
    {
        $calc_data = [
            'xLabel'      => [],
            'impressions' => [],
            'views'       => []
        ];

        $impressions = $this->makeCommonQuery($data, $user)
            ->where('statistics.event', 'impression');

        $views = $this->makeCommonQuery($data, $user);
        $views = $this->makeViewCommonQuery($views, $this->view_event);

        if ($prev_date != '') {
            $curr_time = strtotime((string)now($user->settings->timezone)->toDateTimeString());
            $ago_24 = abs(($curr_time - (60 * 60 * 24)));

            $calc_data['xLabel'] = [date('M j, Y gA', $ago_24)];
            $calc_data['impressions'][date('M j, Y gA', $ago_24)] = 0;
            $calc_data['views'][date('M j, Y gA', $ago_24)] = 0;

            for ($i = 0; $i < 23; $i++) {
                $dStr = date('M j, Y gA', strtotime("-" . $i . " hour", $curr_time));
                $calc_data['xLabel'][] = $dStr;
                $calc_data['impressions'][$dStr] = 0;
                $calc_data['views'][$dStr] = 0;
            }

            if (!is_null($video_id)) {
                $impressions = $impressions->where('video_id', $video_id);
                $views = $views->where('video_id', $video_id);
            }
            $impressions = $impressions->select('id', 'created_at')
                ->whereRaw('date(created_at) <= "' . $curr_date . '"')
                ->whereRaw('date(created_at) > "' . $prev_date . '"')
                ->groupBy('watch_session_id')
                ->groupBy('video_id')
                ->orderBy('created_at');

            $views = $views->select('id', 'created_at')
                ->whereRaw('date(created_at) <= "' . $curr_date . '"')
                ->whereRaw('date(created_at) > "' . $prev_date . '"')
                ->orderBy('created_at');
        } else {
            $impressions = $impressions->select('id', DB::raw('count(id) as impressions'), DB::raw('DATE_FORMAT(created_at, "%l%p") as created_hour'))
                ->whereRaw('date(created_at) = "' . $curr_date . '"')
                ->groupBy('watch_session_id')
                ->groupBy('video_id')
                ->groupBy(DB::raw('hour(created_at)'))
                ->orderBy(DB::raw('hour(created_at)'));

            $views = $views
                ->select('id', DB::raw('count(id) as views'), DB::raw('DATE_FORMAT(created_at, "%l%p") as created_hour'))
                ->whereRaw('date(created_at) = "' . $curr_date . '"')
                ->groupBy(DB::raw('hour(created_at)'))
                ->orderBy(DB::raw('hour(created_at)'));

            $calc_data['xLabel'] = [
                '12AM', '1AM', '2AM', '3AM', '4AM', '5AM', '6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM'
            ];
        }

        $impressions = $impressions->get();
        $views = $views->get();

        if ($prev_date == '') {
            if ($impressions && !is_null($impressions)) {
                foreach ($impressions as $impression) {
                    $calc_data['impressions'][$impression->created_at] = $impression->impressions;
                }
            }

            if ($views && !is_null($views)) {
                foreach ($views as $row) {
                    $calc_data['views'][$row->created_hour] = $row->views;
                }
            }
        } else {
            if ($impressions && !is_null($impressions)) {
                foreach ($impressions as $impression) {
                    $dStr = date('M j, Y gA', strtotime($impression->created_at));
                    if (!isset($calc_data['impressions'][$dStr])) {
                        continue;
                    }

                    $calc_data['impressions'][$dStr] += 1;
                }
            }

            if ($views && !is_null($views)) {
                foreach ($views as $row) {
                    $dStr = date('M j, Y gA', strtotime($row->created_at));
                    if (!isset($calc_data['views'][$dStr])) {
                        continue;
                    }

                    $calc_data['views'][$dStr] += 1;
                }
            }

            $sortTime = [];
            foreach ($calc_data['xLabel'] as $key => $values) {
                $sortTime[] = strtotime($values);
            }
            asort($sortTime);

            $calc_data['xLabel'] = [];
            foreach ($sortTime as $value) {
                $calc_data['xLabel'][] = date('M j, Y gA', $value);
            }

            $calc_data['xLabel'] = array_unique($calc_data['xLabel']);
        }

        return $calc_data;
    }

    /**
     * get overview statistics
     *
     * @param $data
     * @param $user
     * @return array
     */
    public function getStatisticsOverviewData($data, $user)
    {
        $date_ary = $this->getDateRangeData($data, $user);
        $start_date = $date_ary[0];
        $end_date = $date_ary[1];
        $days = $date_ary[2];
        $prev_date = $date_ary[3];
        $today = $date_ary[4];
        $yesterday = $date_ary[5];

        /* ========== Get overview summary data ========= */
        $q_ary = [
            'impression' => $this->getImpressionsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'views'      => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'watch'      => $this->getTotalWatchTime($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'devices'    => $this->getViewedDeviceCount($data, $user, $start_date, $end_date),
        ];
        /* ========== summary end ========= */

        /* ========== Get overview trend data ========= */
        $period = 'month';
        if ($days == 0) {
            $period = 'day';
        }

        $calc_data = [
            'xLabel'      => [],
            'impressions' => [],
            'views'       => []
        ];

        if ($period == 'day') {
            $calc_data = $this->getHoursChartData($data, $user, $start_date);
        } else {
            $log_data = $this->makeCommonQuery($data, $user)
                ->select('id', 'created_at')
                ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
                ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
                ->where('statistics.event', 'impression')
                ->groupBy('statistics.watch_session_id')
                ->groupBy('statistics.video_id')
                ->orderBy('statistics.created_at')
                ->get();

            $view_log = $this->makeCommonQuery($data, $user)
                ->select('statistics.id', 'statistics.created_at')
                ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
                ->where(DB::raw('date(statistics.created_at)'), '>', $start_date);
            $view_log = $this->makeViewCommonQuery($view_log)
                ->orderBy('statistics.created_at')
                ->get();

            $cal_number = abs(strtotime($end_date) - strtotime($start_date));
            $cal_number = round($cal_number / (60 * 60 * 24));
            for ($i = 0; $i <= $cal_number; $i++) {
                $dStr = date('M d, Y', strtotime($end_date . ' - ' . ($cal_number * 1 - $i) . ' days'));
                $calc_data['impressions'][$dStr] = 0;
                $calc_data['views'][$dStr] = 0;
            }

            if ($log_data && !is_null($log_data)) {
                foreach ($log_data as $row) {
                    $dStr = date('M d, Y', strtotime($row->created_at));
                    if (!isset($calc_data['impressions'][$dStr])) {
                        continue;
                    }

                    $calc_data['impressions'][$dStr] += 1;
                }
            }

            if ($view_log && !is_null($view_log)) {
                foreach ($view_log as $row) {
                    $dStr = date('M d, Y', strtotime($row->created_at));
                    if (!isset($calc_data['views'][$dStr])) {
                        continue;
                    }

                    $calc_data['views'][$dStr] += 1;
                }
            }

            $calc_data = $this->calcXLabel($calc_data, $start_date, $end_date, $cal_number);
        }

        $get_data = $this->setTrendDataFormat($calc_data['xLabel'], [
            $calc_data['impressions'], $calc_data['views']
        ]);
        $calc_data['impressions'] = $get_data[0];
        $calc_data['views'] = $get_data[1];
        /* ========== Trend end ========= */

        /* ========== Top videos get ========= */
        $top_videos = $this->getTopVideosList($data, $user, $start_date, $end_date);
        /* ========== Top videos end ========= */

        $curr_date = date('Y-m-d H:i:s', strtotime(now($user->settings->timezone)));
        $prev_24 = date('Y-m-d H:i:s', strtotime(now($user->settings->timezone)->subDay()));

        $top_24_videos = $this->makeCommonQuery($data, $user)
            ->select(DB::raw('COUNT(statistics.video_id) AS `view_count`'), 'videos.id', 'videos.title', DB::raw('videos.video_id AS `video_key`'), 'videos.thumbnail', 'videos.duration')
            ->join('videos', 'statistics.video_id', '=', 'videos.id')
            ->where(DB::raw('date(statistics.created_at)'), '<=', $curr_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $prev_24);

        $top_24_videos = $this->makeViewCommonQuery($top_24_videos, $this->view_event)
            ->orderBy(DB::raw('COUNT(statistics.video_id)'), 'desc')
            ->orderBy('videos.created_at', 'desc')
            ->get()
            ->map(function ($el) use ($data, $user, $curr_date, $prev_24) {
                $obj = $el;
                $obj->show = false;

                $calc_data1 = $this->getHoursChartData($data, $user, $curr_date, $el->id, $prev_24);

                $get_data = $this->setTrendDataFormat($calc_data1['xLabel'], [
                    $calc_data1['impressions'], $calc_data1['views']
                ]);

                $calc_data1['impressions'] = $get_data[0];
                $calc_data1['views'] = $get_data[1];

                $obj->calc_data = $calc_data1;

                return $obj;
            });

        $total_24_charts = $this->getHoursChartData($data, $user, $curr_date, null, $prev_24);
        $get_data1 = $this->setTrendDataFormat($total_24_charts['xLabel'], [
            $total_24_charts['impressions'], $total_24_charts['views']
        ]);

        $total_24_charts['impressions'] = $get_data1[0];
        $total_24_charts['views'] = $get_data1[1];

        return [$q_ary, $calc_data, $top_videos, $top_24_videos, $total_24_charts];
    }

    /**
     * Engagement hourly chart data
     * @param $data
     * @param $user
     * @param $curr_date
     * @param null $video_id
     * @return array
     */
    public function getEngagementHoursChartData($data, $user, $curr_date, $video_id = null)
    {
        $calc_data = [
            'xLabel'         => [],
            'engagement'     => [],
            'watch_time'     => [],
            'view_duration'  => [],
            'click_through'  => [],
            'email_capture'  => [],
            'related_videos' => [],
            'video_length'   => [],
            'views'          => [],
        ];

        $calc_data['xLabel'] = [
            '12AM', '1AM', '2AM', '3AM', '4AM', '5AM', '6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM'
        ];

        for ($i = 0; $i < count($calc_data['xLabel']); $i++) {
            $dStr = $calc_data['xLabel'][$i];
            $calc_data['view_duration'][$dStr] = 0;
            $calc_data['click_through'][$dStr] = 0;
            $calc_data['email_capture'][$dStr] = 0;
            $calc_data['related_videos'][$dStr] = 0;
            $calc_data['views'][$dStr] = 0;

            /**
             * Video length
             */
            $video_length = $this->makeCommonQuery($data, $user)
                ->select('videos.duration')
                ->join('videos', 'statistics.video_id', '=', 'videos.id')
                ->where(DB::raw('date(statistics.created_at)'), $curr_date)
                ->where(DB::raw('hour(statistics.created_at)'), $i);
            $video_length = $this->makeViewCommonQuery($video_length, $this->view_event);
            if (!is_null($video_id)) {
                $video_length = $video_length->where('statistics.video_id', $video_id);
            }
            $video_length = $video_length->pluck('videos.duration')->toArray();
            $calc_data['video_length'][$dStr] = array_sum($video_length);

            /**
             * Watch time
             */
            $watch_end = $this->makeCommonQuery($data, $user)
                ->where(DB::raw('date(statistics.created_at)'), $curr_date)
                ->where(DB::raw('hour(statistics.created_at)'), $i)
                ->where('statistics.event', $this->view_event);
            if (!is_null($video_id)) {
                $watch_end = $watch_end->where('statistics.video_id', $video_id);
            }
            $watch_end = $watch_end->where('statistics.watch_end', '<>', '0')
                ->sum('statistics.watch_end');

            $watch_start = $this->makeCommonQuery($data, $user)
                ->where(DB::raw('date(statistics.created_at)'), $curr_date)
                ->where(DB::raw('hour(statistics.created_at)'), $i)
                ->where('statistics.event', $this->view_event)
                ->where('statistics.watch_end', '<>', '0')
                ->sum('statistics.watch_start');

            $calc_data['watch_time'][$dStr] = abs($watch_end - $watch_start);

            /**
             * Engagement
             */
            if ($calc_data['video_length'][$dStr] != 0) {
                $calc_data['engagement'][$dStr] = round((($calc_data['watch_time'][$dStr] / $calc_data['video_length'][$dStr]) * 100), 2);
            } else {
                $calc_data['engagement'][$dStr] = 0;
            }
            $calc_data['watch_time'][$dStr] = round($calc_data['watch_time'][$dStr] / 60, 2);
        }

        /**
         * total views
         */
        $views = $this->makeCommonQuery($data, $user);
        $views = $this->makeViewCommonQuery($views, $this->view_event)
            ->select('id', DB::raw('count(id) as views'), DB::raw('DATE_FORMAT(created_at, "%l%p") as created_hour'))
            ->whereRaw('date(created_at) = "' . $curr_date . '"')
            ->groupBy(DB::raw('hour(created_at)'))
            ->orderBy(DB::raw('hour(created_at)'));
        if ($views && !is_null($views)) {
            foreach ($views as $row) {
                $calc_data['views'][$row->created_hour] = $row->views;
            }
        }

        /**
         * avg view duration
         */
        foreach ($calc_data['view_duration'] as $key => $row) {
            if ($calc_data['views'][$key] == 0) {
                continue;
            }

            $calc_data['view_duration'][$key] = round((($calc_data['watch_time'][$key] / 60) / $calc_data['views'][$key]), 2);
        }

        /**
         * click through
         */
        $clicks = $this->makeCommonQuery($data, $user);
        $clicks = $this->makeViewCommonQuery($clicks, 'click')
            ->select('id', DB::raw('count(id) as clicks'), DB::raw('DATE_FORMAT(created_at, "%l%p") as created_hour'))
            ->whereRaw('date(statistics.created_at) = "' . $curr_date . '"')
            ->groupBy(DB::raw('hour(statistics.created_at)'))
            ->orderBy(DB::raw('hour(statistics.created_at)'));
        if ($clicks && !is_null($clicks)) {
            foreach ($clicks as $row) {
                $calc_data['click_through'][$row->created_hour] = $row->clicks;
            }
        }

        /**
         * Email Capture
         */
        $email_capture = $this->makeCommonQuery($data, $user);
        $email_capture = $this->makeViewCommonQuery($email_capture, 'email_capture')
            ->select('id', DB::raw('count(id) as email_capture'), DB::raw('DATE_FORMAT(created_at, "%l%p") as created_hour'))
            ->whereRaw('date(created_at) = "' . $curr_date . '"')
            ->groupBy(DB::raw('hour(created_at)'))
            ->orderBy(DB::raw('hour(created_at)'));
        if ($email_capture && !is_null($email_capture)) {
            foreach ($email_capture as $row) {
                $calc_data['email_capture'][$row->created_hour] = $row->email_capture;
            }
        }

        /**
         * Email Capture
         */
        $related_videos = $this->makeCommonQuery($data, $user);
        $related_videos = $this->makeViewCommonQuery($related_videos, ['click', 'email_capture'])
            ->select('id', DB::raw('count(id) as related_videos'), DB::raw('DATE_FORMAT(created_at, "%l%p") as created_hour'))
            ->whereRaw('date(created_at) = "' . $curr_date . '"')
            ->groupBy(DB::raw('hour(created_at)'))
            ->orderBy(DB::raw('hour(created_at)'));
        if ($related_videos && !is_null($related_videos)) {
            foreach ($related_videos as $row) {
                $calc_data['related_videos'][$row->created_hour] = $row->related_videos;
            }
        }

        return $calc_data;
    }
       /**
     * get engagements statistics data.
     *
     * @param $data
     * @param $user
     * @return array
     */
    public function getEngagementsStatisticsDataForVideo($data,$user)
    {
        $date_ary = $this->getDateRangeData($data, $user);
        $start_date = $date_ary[0];
        $end_date = $date_ary[1];
        $days = $date_ary[2];
        $prev_date = $date_ary[3];
        $today = $date_ary[4];
        $yesterday = $date_ary[5];
        $summary = [
            'avg_duration'       => ['total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0],
            'click_through'      => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, 'click'),
            'email_capture'      => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, 'email_capture'),
            'views_count'        => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'watch'              => $this->getTotalWatchTime($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday)
        ];
        foreach ($summary['avg_duration'] as $aE => $val) {
            if ($summary['views_count'][$aE] == 0) {
                continue;
            }

            $summary['avg_duration'][$aE] = round((($summary['watch'][$aE] / 60) / $summary['views_count'][$aE]), 2);
        }
        return $summary;
    }
    /**
     * get engagements statistics data.
     *
     * @param $data
     * @param $user
     * @return array
     */
    public function getEngagementsStatisticsData($data, $user)
    {
        $date_ary = $this->getDateRangeData($data, $user);
        $start_date = $date_ary[0];
        $end_date = $date_ary[1];
        $days = $date_ary[2];
        $prev_date = $date_ary[3];
        $today = $date_ary[4];
        $yesterday = $date_ary[5];

        $summary = [
            'avg_engagement'     => ['total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0],
            'total_video_length' => $this->getTotalViewedVideosLength($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'watch'              => $this->getTotalWatchTime($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'avg_duration'       => ['total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0],
            'play_rate'          => ['total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0],
            'views_count'        => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'click_through'      => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, 'click'),
            'email_capture'      => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, 'email_capture'),
            'related_videos'     => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, ['click', 'email_capture']),
        ];

        $total_impressions = $this->getImpressionsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday);

        foreach ($summary['avg_engagement'] as $aE => $val) {
            if ($summary['total_video_length'][$aE] == 0) {
                continue;
            }

            $summary['avg_engagement'][$aE] = round((($summary['watch'][$aE] / $summary['total_video_length'][$aE]) * 100), 2);
        }

        foreach ($summary['avg_duration'] as $aE => $val) {
            if ($summary['views_count'][$aE] == 0) {
                continue;
            }

            $summary['avg_duration'][$aE] = round((($summary['watch'][$aE] / 60) / $summary['views_count'][$aE]), 2);
        }

        foreach ($summary['play_rate'] as $aE => $val) {
            if ($total_impressions[$aE] == 0) {
                continue;
            }

            $summary['play_rate'][$aE] = round((($summary['views_count'][$aE] / $total_impressions[$aE]) * 100), 2);
        }

        $top_videos = $this->getTopVideosList($data, $user, $start_date, $end_date, ['click', 'email_capture']);
        $top_end_videos = $this->getTopVideosList($data, $user, $start_date, $end_date, ['click']);
        
        $top_videos_engagement = $this->getTopVideosList($data, $user, $start_date, $end_date, ['video_view']);
        $checkedVideos = @$data['checkedVideos'];
        foreach($top_videos_engagement as $key => $value){
            $end_date_tmp = date('Y-m-d', strtotime(now($user->settings->timezone)));
            $start_date_tmp = date('Y-m-d', strtotime('-1 month', strtotime($today)));
            $data['checkedVideos'] = array($value['id']);
            $views_count = $this->getTotalViewsCount($data, $user, $start_date_tmp, $end_date_tmp, $prev_date, $today, $yesterday);
            $value['views_count'] = $views_count['total'];
            $total_impressions = $this->getImpressionsCount($data, $user, $start_date_tmp, $end_date_tmp, $prev_date, $today, $yesterday);
            $value['total_impressions'] = $total_impressions['total'];
            if($value['total_impressions'] == 0){
                $value['avg_engagement'] = 0;
                continue;
            }
            $value['avg_engagement'] = round((($value['views_count'] / $value['total_impressions']) * 100));
        }
        $data['checkedVideos'] = $checkedVideos;
        //SORTING BY PLAY_RATE 
        $AVG_ENG = array();
        $top_videos_engagement = json_decode($top_videos_engagement, true);
        foreach ($top_videos_engagement as $key => $row)
        {
            if($row['avg_engagement'] == 0){
                unset($top_videos_engagement[$key]);
                continue;
            }
            $AVG_ENG[$key] = $row['avg_engagement'];
        }
        array_multisort($AVG_ENG, SORT_DESC, $top_videos_engagement);

        $top_end_videos_engagement = $this->getTopVideosList($data, $user, $start_date, $end_date, ['video_view']);
        $checkedVideos = $data['checkedVideos'];
        foreach($top_end_videos_engagement as $key => $value){
            $end_date = date('Y-m-d', strtotime(now($user->settings->timezone)));
            $start_date = date('Y-m-d', strtotime('-1 month', strtotime($today)));
            $data['checkedVideos'] = array($value['id']);
            $watch = $this->getTotalWatchTime($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday);
            $value['watch'] = $watch['total'];
            $total_video_length = $this->getTotalViewedVideosLength($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday);
            $value['total_video_length'] = $total_video_length['total'];
            if($value['total_video_length'] == 0){
                $value['avg_engagement'] = 0;
                continue;
            }
            $value['avg_engagement'] = round((($value['watch'] / $value['total_video_length']) * 100));
        }
        $data['checkedVideos'] = $checkedVideos;
        //SORTING BY AVG_ENG 
        $AVG_ENG = array();
        $top_end_videos_engagement = json_decode($top_end_videos_engagement, true);
        foreach ($top_end_videos_engagement as $key => $row)
        {
            if($row['avg_engagement'] == 0){
                unset($top_end_videos_engagement[$key]);
                continue;
            }
            $AVG_ENG[$key] = $row['avg_engagement'];
        }
        array_multisort($AVG_ENG, SORT_DESC, $top_end_videos_engagement);

        /** ===== trend data ====== **/

        $period = 'month';
        if ($days == 0) {
            $period = 'day';
        }

        $calc_data = [
            'xLabel'         => [],
            'engagement'     => [],
            'watch_time'     => [],
            'view_duration'  => [],
            'click_through'  => [],
            'email_capture'  => [],
            'related_videos' => [],
            'video_length'   => [],
            'views'          => [],
        ];

        if ($period == 'day') {
            $calc_data = $this->getEngagementHoursChartData($data, $user, $start_date);
        } else {
            $cal_number = abs(strtotime($end_date) - strtotime($start_date));
            $cal_number = round($cal_number / (60 * 60 * 24));
            for ($i = 0; $i <= $cal_number; $i++) {
                $dStr = date('M d, Y', strtotime($end_date . ' - ' . ($cal_number * 1 - $i) . ' days'));
                $dStr1 = date('Y-m-d', strtotime($end_date . ' - ' . ($cal_number * 1 - $i) . ' days'));
                $calc_data['view_duration'][$dStr] = 0;
                $calc_data['click_through'][$dStr] = 0;
                $calc_data['email_capture'][$dStr] = 0;
                $calc_data['related_videos'][$dStr] = 0;
                $calc_data['views'][$dStr] = 0;

                /**
                 * Video Length
                 */
                $video_length = $this->makeCommonQuery($data, $user)
                    ->select('videos.duration')
                    ->join('videos', 'statistics.video_id', '=', 'videos.id')
                    ->where(DB::raw('date(statistics.created_at)'), $dStr1);
                $video_length = $this->makeViewCommonQuery($video_length, $this->view_event)
                    ->pluck('videos.duration')
                    ->toArray();
                $calc_data['video_length'][$dStr] = array_sum($video_length);

                /**
                 * Watch Time
                 */
                $watch_end = $this->makeCommonQuery($data, $user)
                    ->where(DB::raw('date(statistics.created_at)'), $dStr1)
                    ->where('statistics.event', $this->view_event)
                    ->where('statistics.watch_end', '<>', '0')
                    ->sum('watch_end');
                $watch_start = $this->makeCommonQuery($data, $user)
                    ->where(DB::raw('date(statistics.created_at)'), $dStr1)
                    ->where('statistics.event', $this->view_event)
                    ->where('statistics.watch_end', '<>', '0')
                    ->sum('watch_start');
                $calc_data['watch_time'][$dStr] = abs($watch_end - $watch_start);

                /**
                 * AVG. Engagement
                 */
                if ($calc_data['video_length'][$dStr] != 0) {
                    $calc_data['engagement'][$dStr] = round((($calc_data['watch_time'][$dStr] / $calc_data['video_length'][$dStr]) * 100), 2);
                } else {
                    $calc_data['engagement'][$dStr] = 0;
                }
                $calc_data['watch_time'][$dStr] = round($calc_data['watch_time'][$dStr] / 60, 2);
            }

            /**
             * Total views
             */
            $view_log = $this->makeCommonQuery($data, $user)
                ->select('id', 'created_at')
                ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
                ->where(DB::raw('date(statistics.created_at)'), '>', $start_date);
            $view_log = $this->makeViewCommonQuery($view_log)
                ->orderBy('created_at')
                ->get();

            if ($view_log && !is_null($view_log)) {
                foreach ($view_log as $row) {
                    $dStr = date('M d, Y', strtotime($row->created_at));
                    if (!isset($calc_data['views'][$dStr])) {
                        continue;
                    }
                    $calc_data['views'][$dStr] += 1;
                }
            }

            /**
             * AVG. View duration
             */
            foreach ($calc_data['view_duration'] as $key => $row) {
                if ($calc_data['views'][$key] == 0) {
                    continue;
                }

                $calc_data['view_duration'][$key] = round(($calc_data['watch_time'][$key] / $calc_data['views'][$key]), 2);
            }

            /**
             * Click through
             */
            $click_log = $this->makeCommonQuery($data, $user)
                ->select('id', 'created_at')
                ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
                ->where(DB::raw('date(statistics.created_at)'), '>', $start_date);
            $click_log = $this->makeViewCommonQuery($click_log, 'click')
                ->orderBy('created_at')
                ->get();

            if ($click_log && !is_null($click_log)) {
                foreach ($click_log as $row) {
                    $dStr = date('M d, Y', strtotime($row->created_at));
                    if (!isset($calc_data['click_through'][$dStr])) {
                        continue;
                    }
                    $calc_data['click_through'][$dStr] += 1;
                }
            }

            /**
             * Email Capture
             */
            $email_capture = $this->makeCommonQuery($data, $user)
                ->select('id', 'created_at')
                ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
                ->where(DB::raw('date(statistics.created_at)'), '>', $start_date);
            $email_capture = $this->makeViewCommonQuery($email_capture, 'email_capture')
                ->orderBy('created_at')
                ->get();

            if ($email_capture && !is_null($email_capture)) {
                foreach ($email_capture as $row) {
                    $dStr = date('M d, Y', strtotime($row->created_at));
                    if (!isset($calc_data['email_capture'][$dStr])) {
                        continue;
                    }
                    $calc_data['email_capture'][$dStr] += 1;
                }
            }

            /**
             * Related Videos
             */
            $related_videos = $this->makeCommonQuery($data, $user)
                ->select('id', 'created_at')
                ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date)
                ->where(DB::raw('date(statistics.created_at)'), '>', $start_date);
            $related_videos = $this->makeViewCommonQuery($related_videos, ['click', 'email_capture'])
                ->orderBy('created_at')
                ->get();

            if ($related_videos && !is_null($related_videos)) {
                foreach ($related_videos as $row) {
                    $dStr = date('M d, Y', strtotime($row->created_at));
                    if (!isset($calc_data['related_videos'][$dStr])) {
                        continue;
                    }
                    $calc_data['related_videos'][$dStr] += 1;
                }
            }

            $calc_data = $this->calcXLabel($calc_data, $start_date, $end_date, $cal_number);
        }

        $get_data = $this->setTrendDataFormat($calc_data['xLabel'], [
            $calc_data['engagement'], $calc_data['watch_time'], $calc_data['view_duration'],
            $calc_data['click_through'], $calc_data['email_capture'], $calc_data['related_videos']
        ]);

        $calc_data['engagement'] = $get_data[0];
        $calc_data['watch_time'] = $get_data[1];
        $calc_data['view_duration'] = $get_data[2];
        $calc_data['click_through'] = $get_data[3];
        $calc_data['email_capture'] = $get_data[4];
        $calc_data['related_videos'] = $get_data[5];

        /** ===== trend data end ====== **/

        return [$summary, $top_videos, $top_end_videos, $calc_data, $top_videos_engagement, $top_end_videos_engagement];
    }

    /**
     * Gender views
     *
     * @param $data
     * @param $user
     * @param $start_date
     * @param $end_date
     * @param string $gender
     * @return int
     */
    public function getGenderViews($data, $user, $start_date, $end_date, $gender = 'male')
    {
        $gender_views = $this->makeCommonQuery($data, $user)
            ->select('statistics.video_id')
            ->join('subscribers', 'statistics.unique_ref', '=', 'subscribers.user_agent')
            ->where(DB::raw('date(statistics.created_at)'), '>', $start_date)
            ->where(DB::raw('date(statistics.created_at)'), '<=', $end_date);
        $gender_views = $this->makeViewCommonQuery($gender_views)
            ->where('subscribers.gender', $gender)
            ->get()->toArray();

        return sizeof($gender_views);
    }

    /**
     * Get audience statistics data
     * @param $data
     * @param $user
     * @return array
     */
    public function getAudienceStatisticsData($data, $user)
    {
        $date_ary = $this->getDateRangeData($data, $user);
        $start_date = $date_ary[0];
        $end_date = $date_ary[1];
//        $days = $date_ary[2];
        $prev_date = $date_ary[3];
        $today = $date_ary[4];
        $yesterday = $date_ary[5];

        $summary = [
            'unique_views'    => $this->getUniqueViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'view_per_viewer' => ['total' => 0, 'prev' => 0, 'today' => 0, 'yesterday' => 0],
            'contacts'        => $this->getCapturedContacts($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
        ];

        $total_views = $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday);

        foreach ($summary['view_per_viewer'] as $aE => $val) {
            if ($total_views[$aE] == 0) {
                continue;
            }

            $summary['view_per_viewer'][$aE] = round(($total_views[$aE] / $summary['unique_views'][$aE]));
        }

        $top_countries = $this->getViewsTopCountries($data, $user, $start_date, $end_date);
        $top_source_urls = $this->getViewsTopCountries($data, $user, $start_date, $end_date, 'domain');
        $locations = $this->getViewsLocations($data, $user, $start_date, $end_date);
        $devices = $this->getViewedDeviceCount($data, $user, $start_date, $end_date);
        $male_views = $this->getGenderViews($data, $user, $start_date, $end_date);
        $female_views = $this->getGenderViews($data, $user, $start_date, $end_date, 'female');
        $total_gender = $male_views + $female_views;
        $gender_views = [
            'male'   => $male_views,
            'female' => $female_views,
            'total'  => $total_gender,
        ];

        return [$summary, $top_countries, $top_source_urls, $locations, $devices, $gender_views];
    }

    /**
     * Dashboard data
     *
     * @param $data
     * @param $user
     * @return array
     */
    public function getDashboardStatisticsData($data, $user)
    {
        $date_ary = $this->getDateRangeData($data, $user);
        $start_date = $date_ary[0];
        $end_date = $date_ary[1];
        $prev_date = $date_ary[3];
        $today = $date_ary[4];
        $yesterday = $date_ary[5];

        /* ========== Get overview summary data ========= */
        $q_ary = [
            'impression'    => $this->getImpressionsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'views'         => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'watch'         => $this->getTotalWatchTime($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday),
            'devices'       => $this->getViewedDeviceCount($data, $user, $start_date, $end_date),
            'click_through' => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, 'click'),
            'email_capture' => $this->getTotalViewsCount($data, $user, $start_date, $end_date, $prev_date, $today, $yesterday, 'email_capture'),
        ];

        /* ========== Top videos get ========= */
        $top_videos = $this->getTopVideosList($data, $user, $start_date, $end_date, ['video_view'], 5);
        /* ========== Top videos end ========= */

        $curr_date = date('Y-m-d', strtotime(now($user->settings->timezone)));
        $prev_24 = date('Y-m-d', strtotime(now($user->settings->timezone)->subDay()));
        $top_24_videos = $this->makeCommonQuery($data, $user)
            ->select(DB::raw('COUNT(statistics.video_id) AS `view_count`'), 'videos.id', 'videos.title', DB::raw('videos.video_id AS `video_key`'), 'videos.thumbnail', 'videos.duration')
            ->join('videos', 'statistics.video_id', '=', 'videos.id')
            ->where(DB::raw('date(statistics.created_at)'), '<=', $curr_date)
            ->where(DB::raw('date(statistics.created_at)'), '>', $prev_24);
        $top_24_videos = $this->makeViewCommonQuery($top_24_videos, $this->view_event)
            ->orderBy(DB::raw('COUNT(statistics.video_id)'), 'desc')
            ->orderBy('videos.created_at', 'desc')
            ->get()
            ->take(5)
            ->map(function ($el) use ($data, $user, $curr_date, $prev_24) {
                $obj = $el;
                $obj->show = false;

                $calc_data1 = $this->getHoursChartData($data, $user, $curr_date, $el->id, $prev_24);

                $get_data = $this->setTrendDataFormat($calc_data1['xLabel'], [
                    $calc_data1['impressions'], $calc_data1['views']
                ]);

                $calc_data1['impressions'] = $get_data[0];
                $calc_data1['views'] = $get_data[1];

                $obj->calc_data = $calc_data1;

                return $obj;
            });

        $total_24_charts = $this->getHoursChartData($data, $user, $curr_date, null, $prev_24);
        $get_data1 = $this->setTrendDataFormat($total_24_charts['xLabel'], [
            $total_24_charts['impressions'], $total_24_charts['views']
        ]);

        $total_24_charts['impressions'] = $get_data1[0];
        $total_24_charts['views'] = $get_data1[1];

        $top_countries = $this->getViewsTopCountries($data, $user, $start_date, $end_date, 'country_name', 5);

        return [$q_ary, $top_videos, $top_24_videos, $total_24_charts, $top_countries];
    }
}
