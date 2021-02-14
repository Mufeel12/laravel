<?php

namespace App\Http\Controllers\Api;

use App\Experiment\VideoExperiment;
use App\Http\Controllers\Controller;
use App\Repositories\Statistics\StatisticsRepository;
use App\Statistic;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    protected $statisticsRepo;

    public function __construct(StatisticsRepository $statisticsRepository)
    {
        $this->statisticsRepo = $statisticsRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $data = [
            'start_date' => $request->input('start_date')
                ? $request->input('start_date')
                : date('Y-m-d H:i:s', strtotime('-1 month')),
            'end_date'   => $request->input('end_date')
                ? $request->input('end_date')
                : date('Y-m-d H:i:s'),
        ];

        if ($request->has('checkedProjects')) {
            $data['checkedProjects'] = $request->input('checkedProjects');
        }
        if ($request->has('checkedVideos')) {
            $data['checkedVideos'] = $request->input('checkedVideos');
        }
        if ($request->has('checkedCountries')) {
            $data['checkedCountries'] = $request->input('checkedCountries');
        }
        if ($request->has('checkedDomains')) {
            $data['checkedDomains'] = $request->input('checkedDomains');
        }
        if ($request->has('checkedDevices')) {
            $data['checkedDevices'] = $request->input('checkedDevices');
        }

        $active = $request->input('active');

        if ($active == 'overview') {
            $re = $this->statisticsRepo->getStatisticsOverviewData($data, $request->user());
            $r_data = [
                'summary'         => $re[0],
                'trend'           => $re[1],
                'top_videos'      => $re[2],
                'top_24_videos'   => $re[3],
                'total_24_charts' => $re[4],
            ];
        } else if ($active == 'engagement') {
            $re = $this->statisticsRepo->getEngagementsStatisticsData($data, $request->user());
            $r_data = [
                'summary'        => $re[0],
                'top_videos'     => $re[1],
                'top_end_videos' => $re[2],
                'trend'          => $re[3],
                'top_videos_engagement'=> $re[4],
                'top_end_videos_engagement'=> $re[5],
            ];
        } else {
            $re = $this->statisticsRepo->getAudienceStatisticsData($data, $request->user());
            $r_data = [
                'summary'         => $re[0],
                'top_countries'   => $re[1],
                'top_domains'     => $re[2],
                'views_locations' => $re[3],
                'views_devices'   => $re[4],
                'gender'          => $re[5],
            ];
        }

        return response()->json($r_data);
    }

 /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexAllData(Request $request)
    {
        $data = [
            'start_date' =>  date('Y-m-d H:i:s', strtotime('-10 year')),
            'end_date'   => date('Y-m-d H:i:s'),
        ];
        
        if ($request->has('checkedProjects')) {
            $data['checkedProjects'] = $request->input('checkedProjects');
        }
        if ($request->has('checkedVideos')) {
            $data['checkedVideos'] = $request->input('checkedVideos');
        }
        if ($request->has('checkedCountries')) {
            $data['checkedCountries'] = $request->input('checkedCountries');
        }
        if ($request->has('checkedDomains')) {
            $data['checkedDomains'] = $request->input('checkedDomains');
        }
        if ($request->has('checkedDevices')) {
            $data['checkedDevices'] = $request->input('checkedDevices');
        }

        $active = $request->input('active');

            $re = $this->statisticsRepo->getStatisticsOverviewData($data, $request->user());
            $r_data = [
                'summary'         => $re[0],
                'trend'           => $re[1],
                'top_videos'      => $re[2],
                'top_24_videos'   => $re[3],
                'total_24_charts' => $re[4],
            ];
       
     
        $re3 = $this->statisticsRepo->getEngagementsStatisticsDataForVideo($data, $request->user());
        $r_data['summary_engagment'] = $re3;
        
        
        return response()->json($r_data);
    }

    /**
     * Get Dashboard Statistics data.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStatistics(Request $request)
    {
        $data = [
            'start_date' => $request->input('start_date')
                ? $request->input('start_date')
                : date('Y-m-d H:i:s', strtotime('-1 month')),
            'end_date'   => $request->input('end_date')
                ? $request->input('end_date')
                : date('Y-m-d H:i:s'),
        ];

        $re = $this->statisticsRepo->getDashboardStatisticsData($data, $request->user());
        $r_data = [
            'summary'         => $re[0],
            'top_videos'      => $re[1],
            'top_24_videos'   => $re[2],
            'total_24_charts' => $re[3],
            'top_countries'   => $re[4],
        ];

        return response()->json($r_data);
    }

    /**
     * Project, video statistics
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatisticsByProject(Request $request)
    {
        $data = [
            'start_date' => $request->input('start_date')
                ? $request->input('start_date')
                : date('Y-m-d H:i:s', strtotime('-1 month')),
            'end_date'   => $request->input('end_date')
                ? $request->input('end_date')
                : date('Y-m-d H:i:s'),
        ];

        if ($request->has('project_id')) {
            $data['project_id'] = $request->input('project_id');
        }

        if ($request->has('video_id')) {
            $data['video_id'] = $request->input('video_id');
        }

        $re = $this->statisticsRepo->getStatisticsOverviewData($data, $request->user());
        $r_data = [
            'summary'         => $re[0],
            'trend'           => $re[1],
            'top_videos'      => $re[2],
            'top_24_videos'   => $re[3],
            'total_24_charts' => $re[4],
        ];

        return response()->json($r_data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSources(Request $request)
    {
        $domains = $this->statisticsRepo->getResourceUrls($request->user());

        return response()->json($domains);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'video_id'   => 'required',
            'project_id' => 'required',
            'user_id'    => 'required',
            'team_id'    => 'required'
        ]);

        $videoId = $request->input('video_id');
        $videExperimentId = false;
        if ($videoId) {
            $videoEx = VideoExperiment::where([
                'active'        => 1
            ])->where(function ($q) use ($videoId) {
                $q->where('video_id_a', $videoId);
                $q->orWhere('video_id_b', $videoId);
            })->first();
            if ($videoEx && isset($videoEx->id)) {
                $videExperimentId = $videoEx->id;
            }
        }

        Statistic::record_event([
            'video_id'                => $request->input('video_id'),
            'project_id'              => $request->input('project_id'),
            'user_id'                 => $request->input('user_id'),
            'team_id'                 => $request->input('team_id'),
            'event'                   => $request->input('event'),
            'objectType'              => $request->input('objectType'),
            'event_offset_time'       => $request->input('event_offset_time'),
            'event_interaction_group' => $request->input('event_interaction_group'),
            'watch_start'             => is_null($request->input('watch_start')) ? 0 : $request->input('watch_start'),
            'watch_end'               => is_null($request->input('watch_end')) ? 0 : $request->input('watch_end'),
            'watch_session_id'        => $request->input('watch_session_id'),
            'experiment_id'           => $request->input('experiment_id'),
            'experiment_click_id'     => $request->input('experiment_click_id'),
            'video_experiment_id'     => $videExperimentId
        ], $request);

        return response('success');
    }
}
