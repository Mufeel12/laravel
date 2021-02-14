<?php


namespace App\Repositories\Contacts;

use App\ContactAutoTag;
use App\ContactAutoTagCondition;
use App\Repositories\AbstractRepository;
use App\Statistic;
use App\Subscriber;
use App\Video;
use Illuminate\Support\Facades\DB;
use function GuzzleHttp\json_encode;

class ContactsRepository extends AbstractRepository
{
    protected $model;

    public function __construct(Subscriber $model)
    {
        parent::__construct($model);
    }

    /**
     * public subscriber model
     *
     * @return \Illuminate\Foundation\Application|mixed
     */
    public function model()
    {
        return app(Subscriber::class);
    }

    /**
     * contact auto_tag_model
     *
     * @return \Illuminate\Foundation\Application|mixed
     */
    public function auto_tag_model()
    {
        return app(ContactAutoTag::class);
    }

    /**
     * contacts auto_tag_condition_model
     *
     * @return \Illuminate\Foundation\Application|mixed
     */
    public function auto_tag_condition_model()
    {
        return app(ContactAutoTagCondition::class);
    }

    public function statistics_model()
    {
        return app(Statistic::class);
    }

    /**
     * get video lists
     * @param $user
     * @return mixed
     */
    public function getFullVideosListByUserId($user)
    {
        $query = Video::where('team', $user->currentTeam()->id)->orderBy('created_at', 'desc')->get();

        $query->map(function ($el) {
            $el->last_modified = date('M j, Y', strtotime($el->updated_at));

            return $el;
        });

        return $query;
    }

    private function getWatchSumQuery($subscriber)
    {
        $query = $this->statistics_model()
            ->where('statistics.team_id', $subscriber->team_id)
            ->where('statistics.unique_ref', $subscriber->user_agent)
            ->where('statistics.event', 'video_view')
            ->where(function ($q) {
                $q->where('statistics.watch_start', '<>', '0')
                    ->orWhere('statistics.watch_end', '<>', '0');
            })
            ->where('statistics.watch_end', '<>', '0');

        return $query;
    }

    /**
     * get watched sum per subscribers
     *
     * @param $subscriber
     * @return mixed
     */
    public function getWatchSumBySubscriber($subscriber)
    {
        $query = $this->getWatchSumQuery($subscriber)
            ->select('statistics.city', 'statistics.country_code')
            ->groupBy('statistics.unique_ref')
            ->first();

        $count_video = $this->getWatchSumQuery($subscriber)->groupBy('statistics.video_id')->get()->toArray();
        $count_video = sizeof($count_video);

        $watch_start = (int)$this->getWatchSumQuery($subscriber)->sum('statistics.watch_end');
        $watch_end = (int)$this->getWatchSumQuery($subscriber)->sum('statistics.watch_start');
        $watch_time = abs($watch_end - $watch_start);

        return [
            'city'         => $query ? $query->city : '',
            'country_code' => $query ? $query->country_code : '',
            'count_video'  => $count_video ? $count_video : 0,
            'watch_time'   => $watch_time ? $watch_time : 0,
        ];
    }

    /**
     * get watched history
     *
     * @param $id
     * @param $query
     * @return mixed
     */
    public function getWatchHistoryBySubscriber($id, $query)
    {
        $subscriber = $this->model->find($id);

        $data = $this->statistics_model()
            ->select(
                'statistics.*', 'videos.title', DB::raw('videos.video_id as video_v_id'), DB::raw('videos.id as video_key'), 'videos.thumbnail', 'videos.path', 'videos.duration', 'videos.duration_formatted', DB::raw("(SELECT count(*) FROM statistics WHERE event='click' AND unique_ref = '$subscriber->user_agent' GROUP BY event) as click_event_count"), DB::raw("(SELECT count(*) FROM statistics WHERE event='email_capture' AND unique_ref = '$subscriber->user_agent' GROUP BY event) as email_capture_event_count"), 'video_player_options.interaction_before_email_capture', 'video_player_options.interaction_during_time', 'video_player_options.interaction_during_active', 'video_player_options.interaction_during_type', 'video_player_options.interaction_during_email_capture', 'video_player_options.interaction_during_email_capture_time', 'video_player_options.interaction_after_email_capture'
            )
            ->leftJoin('videos', 'statistics.video_id', '=', 'videos.id')
            ->leftJoin('video_player_options', 'video_player_options.video_id', '=', 'videos.id')
            ->where('statistics.unique_ref', $subscriber->user_agent)
            ->groupBy('statistics.video_id')
            ->orderBy('statistics.created_at', 'desc')
            ->get()
            ->map(function ($el) {
                $el->logged_time = date('M j, Y', strtotime($el->created_at)) . ' at ' . date('g:i A', strtotime($el->created_at));
                $el->history_value = 0;
                $el->watch_action = [
                    '0'   => [
                        'label'  => '0:00',
                        'styles' => [
                            'width'           => '7%',
                            'backgroundColor' => '#F7AA72'
                        ]
                    ],
                    '7'   => [
                        'label'  => '',
                        'styles' => [
                            'width'           => '33%',
                            'backgroundColor' => '#E5FF9F'
                        ]
                    ],
                    '40'  => [
                        'label'  => '09:32',
                        'styles' => [
                            'width'           => '14%',
                            'backgroundColor' => '#FFDC71'
                        ]
                    ],
                    '54'  => [
                        'label'  => '',
                        'styles' => [
                            'width'           => '17%',
                            'backgroundColor' => '#F28D44'
                        ]
                    ],
                    '71'  => [
                        'label'  => '18:22',
                        'styles' => [
                            'width'           => '21%',
                            'backgroundColor' => '#E5FF9F'
                        ]
                    ],
                    '82'  => [
                        'label'  => '',
                        'styles' => [
                            'width'           => '7%',
                            'backgroundColor' => 'transparent'
                        ]
                    ],
                    '100' => [
                        'label'  => '27:01',
                        'styles' => [
                            'width'           => '0',
                            'backgroundColor' => '#E5FF9F'
                        ],
                    ]
                ];

                return $el;
            });

        $history = $data;

        foreach($history as $key => $value)
        {
            $duration = $value['duration'];
            $sec_lap = ceil($duration/10);
            $xLabelNew = array();
            $engagement_single = array();
            for($i=0; $i<=$sec_lap; $i++){
                // $j = $i;
                if($i==0){
                    $results = DB::select( DB::raw("SELECT count(video_id) as vid_count FROM `statistics` WHERE `video_id` IN ('$value[video_id]') AND `event` in ('video_view') AND `watch_start` <> `watch_end` AND `watch_end` <> 0 AND ((`watch_start` >= 0 AND `watch_end` <= 10) OR (`watch_start` <= 0 AND `watch_end` >= 10))") );
                }
                else{
                    $start = ($i*10)+1;
                    $end = ($i+1)*10;
                    $results = DB::select( DB::raw("SELECT count(video_id) as vid_count FROM `statistics` WHERE `video_id` IN ('$value[video_id]') AND `event` in ('video_view') AND `watch_start` <> `watch_end` AND `watch_end` <> 0 AND ((`watch_start` >= $start AND `watch_end` <= $end) OR (`watch_start` <= $start AND `watch_end` >= $end))") );
                }
                // $i = $j;
                // $query = \DB::getQueryLog();
                $results = json_decode(json_encode($results), true);
                foreach($results as $result){
                    $engagement_single[] = $result['vid_count'];
                    // if($i==0){
                    //     $max = $result['vid_count'];
                    //     $engagement_single[] = 100;
                    // }
                    // else{
                        // $engagement_single[] = $result['vid_count'] ? number_format(($result['vid_count']*100)/$max, 2) : 0;
                    // }
                }
                $t_sec = $i*10;
                if($t_sec>$duration){
                    $sec = $duration%60;
                    $min = floor($duration/60);
                    if($min > 60){
                        $min = $min%60;
                        $hour = floor($min/60);
                        $xLabelNew[] = ($hour < 10 ? "0".$hour : $hour).":".($min < 10 ? "0".$min : $min).":".($sec < 10 ? "0".$sec : $sec);
                    }
                    else{
                        $xLabelNew[] = ($min < 10 ? "0".$min : $min).":".($sec < 10 ? "0".$sec : $sec);
                    }
                }
                else{
                    $sec = $t_sec%60;
                    $min = floor($t_sec/60);
                    if($min > 60){
                        $min = $min%60;
                        $hour = floor($min/60);
                        $xLabelNew[] = ($hour < 10 ? "0".$hour : $hour).":".($min < 10 ? "0".$min : $min).":".($sec < 10 ? "0".$sec : $sec);
                    }
                    else{
                        $xLabelNew[] = ($min < 10 ? "0".$min : $min).":".($sec < 10 ? "0".$sec : $sec);
                    }
                }
            }

            $heatmap['history_value'] = 0;
            $i=0;
            $j=1;
            $xLabelNewCount = count($xLabelNew);
            $count = 100/$xLabelNewCount;
            $heatmap_tmp = array();
            $watch_action = array();

            $interaction_before_email_capture = false;
            if($value['interaction_before_email_capture'] == true){
                $heatmap_tmp['label'] = '';
                $heatmap_tmp['value'] = (int)$value['email_capture_event_count']." Emails captured";
                $heatmap_tmp['styles']['width'] = "0.1%";
                $heatmap_tmp['styles']['height'] = '68px';
                $heatmap_tmp['styles']['left'] = "0%";
                $heatmap_tmp['styles']['backgroundColor'] = "transparent";
                $heatmap_tmp['styles']['border'] = "1px dotted black";
                $heatmap_tmp['styles']['position'] = "absolute";
                $heatmap_tmp['styles']['z-index'] = "1";
                // $heatmap_tmp['title'] == true;
                $watch_action['0.1'] = $heatmap_tmp;
                $heatmap_tmp = array();
            }

            foreach($xLabelNew as $key => $xLabel){
                $left = ($i*$count);

                if($value['interaction_during_active'] == true && ((($i*10) == $value['interaction_during_time']) || (($i*10) < $value['interaction_during_time'] && ($j*10) > $value['interaction_during_time']))){
                    $heatmap_tmp['label'] = '';
                    $heatmap_tmp['value'] = (int)$value['click_event_count']." Link Clickthroughs";
                    $heatmap_tmp['styles']['width'] = "0.1%";
                    $heatmap_tmp['styles']['height'] = '68px';
                    $heatmap_tmp['styles']['left'] = $left."%";
                    $heatmap_tmp['styles']['backgroundColor'] = "transparent";
                    $heatmap_tmp['styles']['border'] = "1px dotted black";
                    $heatmap_tmp['styles']['position'] = "absolute";
                    $heatmap_tmp['styles']['z-index'] = "1";
                    // $heatmap_tmp['title'] == true;
                    $watch_action[$left.'.1'] = $heatmap_tmp;
                    $heatmap_tmp = array();
                }

                if($xLabelNewCount>10){
                    if($i%2){
                        $heatmap_tmp['label'] = '';
                    }
                    else{
                        $heatmap_tmp['label'] = $xLabel;
                    }
                }
                else{
                    $heatmap_tmp['label'] = $xLabel;
                }
                $heatmap_tmp['value'] = $engagement_single[$key];
                $heatmap_tmp['styles']['width'] = $count."%"; //$engagement_single[$key];
                $heatmap_tmp['styles']['height'] = '68px';
                $heatmap_tmp['styles']['left'] = $left."%";
                if($engagement_single[$key]==0){
                    $backgroundColor = '#F6F7F9';
                }
                else if($engagement_single[$key]==1){
                    $backgroundColor = '#E5FF9F';
                }
                else if($engagement_single[$key]==2){
                    $backgroundColor = '#FCE77C';
                }
                else if($engagement_single[$key]==3){
                    $backgroundColor = '#FFDC71';
                }
                else if($engagement_single[$key]==4){
                    $backgroundColor = '#F7AA72';
                }
                else if($engagement_single[$key]>4){
                    $backgroundColor = '#F28D44';
                }
                $heatmap_tmp['styles']['backgroundColor'] = $backgroundColor;
                $heatmap_tmp['styles']['border'] = "";
                // $heatmap_tmp['title'] == false;

                $watch_action[$left] = $heatmap_tmp;
                $i++;
                $j++;
            }
            $value['watch_action'] = $watch_action;
        }
        return $history;
    }

    public function getTagHistoryBySubscriber($id, $query)
    {
        $subscriber = $this->model->find($id);

        $data = $this->statistics_model()
            ->select(
                'video_player_options.interaction_before_email_capture_email_tags', 'video_player_options.interaction_during_email_capture_email_tags', 'video_player_options.interaction_after_email_capture_email_tags'
            )
            ->leftJoin('videos', 'statistics.video_id', '=', 'videos.id')
            ->leftJoin('video_player_options', 'statistics.video_id', '=', 'video_player_options.video_id')
            ->where('statistics.unique_ref', $subscriber->user_agent)
            ->groupBy('statistics.video_id')
            ->orderBy('statistics.created_at', 'desc')
            ->get()
            ->map(function ($el) {
                //$el->all_tag = [];
                $el->interaction_before_email_capture_email_tags = json_decode($el->interaction_before_email_capture_email_tags);
                $el->interaction_during_email_capture_email_tags = json_decode($el->interaction_during_email_capture_email_tags);
                $el->interaction_after_email_capture_email_tags = json_decode($el->interaction_after_email_capture_email_tags);
                //array_push($el->all_tag, json_decode($el->interaction_before_email_capture_email_tags)[0], json_decode($el->interaction_during_email_capture_email_tags)[0], json_decode($el->interaction_after_email_capture_email_tags)[0]);
                return $el;
            });

        return $data;
    }

    /**
     * build condition Queries to get contacts
     *
     * @param $s_q
     * @param $row
     * @return mixed
     */
    private function buildQueries($s_q, $row)
    {
        if ($row['end_date'] == '' || is_null($row['end_date']) || $row['end_date'] == '0000-00-00') {
            $end_date = date('Y-m-d H:i:s');
        } else {
            $end_date = date('Y-m-d H:i:s', strtotime($row['end_date']));
        }

        $start_date = date('Y-m-d H:i:s', strtotime($row['start_date']));

        if ($row['watched'] == 'watched') {
            $s_q->whereBetween('statistics.created_at', [$start_date, $end_date])
                ->where('statistics.event', 'video_view');

            if (count($row['specific_videos']) > 0) {
                $s_q = $s_q->whereIn('statistics.video_id', $row['specific_videos']);
            }
        } elseif ($row['watched'] == 'not_watch') {
            $s_q->whereNotBetween('statistics.created_at', [$start_date, $end_date])
                ->where('statistics.event', 'video_view');

            if (count($row['specific_videos']) > 0) {
                $s_q = $s_q->whereNotIn('statistics.video_id', $row['specific_videos']);
            }
        } elseif ($row['watched'] == 'clicked_link') {
            $s_q->whereBetween('statistics.created_at', [$start_date, $end_date])
                ->where('statistics.event', 'click');

            if (count($row['specific_videos']) > 0) {
                $s_q = $s_q->whereNotIn('statistics.video_id', $row['specific_videos']);
            }
        } elseif ($row['watched'] == 'not_click') {
            $s_q->whereNotBetween('statistics.created_at', [$start_date, $end_date])
                ->where('statistics.event', 'click');

            if (count($row['specific_videos']) > 0) {
                $s_q = $s_q->whereNotIn('statistics.video_id', $row['specific_videos']);
            }
        } elseif ($row['watched'] == 'subscribed') {
            $s_q->whereBetween('statistics.created_at', [$start_date, $end_date])
                ->where('statistics.event', 'email_capture');

            if (count($row['specific_videos']) > 0) {
                $s_q = $s_q->whereNotIn('statistics.video_id', $row['specific_videos']);
            }
        } elseif ($row['watched'] == 'not_subscribe') {
            $s_q->whereNotBetween('statistics.created_at', [$start_date, $end_date])
                ->where('statistics.event', 'email_capture');

            if (count($row['specific_videos']) > 0) {
                $s_q = $s_q->whereNotIn('statistics.video_id', $row['specific_videos']);
            }
        }

        return $s_q;
    }

    /**
     * get contacts by condition
     * @param $user
     * @param $tag
     * @param $conditions
     * @param bool $json
     * @return array
     */
    public function getContactCountsByCondition($user, $tag, $conditions, $json = false)
    {
        $query = $this->model->select('subscribers.*', 'statistics.event')
            ->leftJoin('statistics', function ($join) {
                $join->on('subscribers.team_id', '=', 'statistics.team_id')
                    ->on('subscribers.user_agent', '=', 'statistics.unique_ref');
            })
            ->where('subscribers.team_id', $user->currentTeam()->id)
            ->where(function ($q) use ($conditions, $json) {
                foreach ($conditions as $key => $row) {
                    if ($json) {
                        $row['specific_videos'] = json_decode($row['specific_videos'], true);
                    }

                    if ($key > 0 && $row['combination'] == 'OR') {
                        $q->orWhere(function ($s_q) use ($row) {
                            $s_q = $this->buildQueries($s_q, $row);

                            return $s_q;
                        });
                    } else {
                        $q->where(function ($s_q) use ($row) {
                            $s_q = $this->buildQueries($s_q, $row);

                            return $s_q;
                        });
                    }
                }
            })
            ->groupBy('subscribers.email')
            ->orderBy('subscribers.created_at', 'desc');

        $total = $query->get()->map(function ($el) use ($user) {
            $el->last_modify = time_elapsed_string($el->created_at, false, $user->settings->timezone);

            $el->tags = json_decode($el->tags, true);

            return $el;
        })->toArray();

        if (!is_null($tag) && $tag != '') {
            $query = $query->whereRaw('JSON_CONTAINS(subscribers.tags, \'["' . $tag . '"]\')');
            $have = $query->get()->map(function ($el) {
                $el->last_modify = time_elapsed_string($el->created_at);

                $el->tags = json_decode($el->tags, true);

                return $el;
            })->toArray();
        } else {
            $have = [];
        }

        $f_contacts = $this->model->where('team_id', $user->currentTeam()->id)->count();

        return [
            'total'      => $total,
            'have'       => $have,
            'f_contacts' => $f_contacts
        ];
    }

    /**
     * create auto tags
     * @param $request
     * @param $user
     */
    public function createAutoTag($request, $user)
    {
        $conditions = $request->input('conditions');

        $this->setTagsToSubscriber($request, $user, $conditions);

        $auto_tag_data = [
            'user_id'        => $user->id,
            'title'          => $request->input('title'),
            'tag'            => $request->input('tag'),
            'contact_filter' => $request->input('contact_filter'),
            'push_tag_able'  => $request->input('push_tag_able'),
            'tag_color'      => $request->input('tag_color'),
            'tag_action'     => $request->input('tag_action'),
            'active'         => 1,
            'completed'      => ($request->input('contact_filter') == 'current') ? 1 : 0,
        ];

        $auto_tag_id = $this->auto_tag_model()->insertGetId($auto_tag_data);

        foreach ($conditions as $condition) {
            $s_data = [
                'auto_tag_id'     => $auto_tag_id,
                'watched'         => $condition['watched'],
                'video_type'      => $condition['video_type'],
                'project_id'      => $condition['project_id'],
                'specific_videos' => json_encode($condition['specific_videos']),
                'timeline_type'   => $condition['timeline_type'],
                'start_date'      => $condition['start_date'],
                'end_date'        => $condition['end_date'],
                'combination'     => $condition['combination'],
            ];

            $this->auto_tag_condition_model()->insertGetId($s_data);
        }
    }

    /**
     * update auto tag
     *
     * @param $request
     * @param $user
     */
    public function updateAutoTag($request, $user)
    {
        $conditions = $request->input('conditions');

        $this->setTagsToSubscriber($request, $user, $conditions);

        $params = $request->all();
        $params['completed'] = ($request->input('contact_filter') == 'current') ? 1 : 0;

        $auto_contact = $this->auto_tag_model()->find($request->input('id'));
        $auto_contact->fill($params);
        $auto_contact->save();

        foreach ($conditions as $condition) {
            $condition['specific_videos'] = json_encode($condition['specific_videos']);

            $auto_tag_condition = $this->auto_tag_condition_model()->find($condition['id']);
            $auto_tag_condition->fill($condition);
            $auto_tag_condition->save();
        }
    }

    /**
     * set auto tags to subscribers
     *
     * @param $request
     * @param $user
     * @param $conditions
     */
    private function setTagsToSubscriber($request, $user, $conditions)
    {
        $contacts = $this->getContactCountsByCondition($user, $request->input('tag'), $conditions);

        if ($request->input('contact_filter') == 'current') {
            if (count($contacts['total']) > 0) {
                foreach ($contacts['total'] as $key => $value) {
                    $tags_ary = $value['tags'];
                    if (array_search($value, $contacts['have']) === false) {
                        if (!$request->input('tag_action')) {
                            if (array_search($tags_ary, $request->input('tag')) === false) {
                                array_push($tags_ary, $request->input('tag'));
                            }
                        } else {
                            if (($key = array_search($request->input('tag'), $tags_ary)) !== false) {
                                unset($tags_ary[$key]);
                            }
                        }

                        $this->model()
                            ->where('id', $value['id'])
                            ->update([
                                'tags' => json_encode($tags_ary)
                            ]);
                    }
                }
            }
        } else {
            $contacts1 = $this->model()->where('team_id', $user->currentTeam()->id)->get();
            foreach ($contacts1 as $key => $value) {
                $tags_ary = json_decode($value->tags, true);
                if ($request->input('contact_filter') == 'both') {
                    $haystack = $contacts['have'];
                } else {
                    $haystack = $contacts['total'];
                }

                if (array_search($value, $haystack) === false) {
                    if (!$request->input('tag_action')) {
                        array_push($tags_ary, $request->input('tag'));
                    } else {
                        if (($key = array_search($request->input('tag'), $tags_ary)) !== false) {
                            unset($tags_ary[$key]);
                        }
                    }

                    $subscriber = $this->model()->find($value->id);
                    $subscriber->tags = json_encode($tags_ary);
                    $subscriber->save();
                }
            }
        }
    }
}
