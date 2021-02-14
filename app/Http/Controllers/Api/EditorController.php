<?php

namespace App\Http\Controllers\Api;

use App\Comments;
use App\ContentSecurityLog;
use App\Experiment\ThumbnailClickCount;
use App\Experiment\ThumbnailExperiment;
use App\Experiment\VideoExperiment;
use App\Http\Controllers\Controller;
use App\Image;
use App\Thumbnail;
use App\TranslatedSubtitle;
use App\User;
use App\Video;
use App\Project;
use App\SnapPage;
use App\VideoBasecode;
use App\VideoChapter;
use App\VideoDescription;
use App\VideoEventcode;
use App\VideoPlayerOption;
use App\VideoRelevantThumbnail;
use App\VideoSubtitle;
use App\VideoThumbnailScrumb;
use App\VideoPublishSchedular;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Spark\Repositories\TeamRepository;
use Laravel\Spark\Spark;
use App\Http\Controllers\Api\Traits\ExperimentTrait;
use Aws\S3\S3Client;
use App\ExperimentBrowserCookie;
use Illuminate\Support\Str;

class EditorController extends Controller
{
    /**
     * Return an array with all editor data
     *
     * @param Request $request
     * @return array
     */
    use ExperimentTrait;

    public function show(Request $request)
    {
        $user = $request->user();
        $id = $request->input('id');
        $ip = $request->getClientIp();
        $geo_location = geoip()->getLocation($ip);
        // Get video
        $video = Video::with('schedular')->where('id', $id)->orWhere('video_id', $id)->first();
        if (is_null($video)) {
            return response(['success' => false, 'message' => 'Video not found']);
        }

//store drm visual and forensic session count
        $sessions_counts = [];
        if($video->drm_protection == 1){
            if($video->drm_sessions_count == null){
                $drm_sessions_count = 1;
            }else{
                $drm_sessions_count = ($video->drm_sessions_count + 1);
            }
            $sessions_counts['drm_sessions_count'] = $drm_sessions_count;
        }

        if($video->forensic_watermarking == 1) {

            if ($video->forensic_sessions_count == null) {
                $forensic_sessions_count = 1;
            } else {
                $forensic_sessions_count = ($video->forensic_sessions_count + 1);
            }
            $sessions_counts['forensic_sessions_count'] = $forensic_sessions_count;
        }
        if($video->visual_watermarking == 1){
            $session_id = Str::random(16);
            ContentSecurityLog::create([
                'type' => 'visual',
                'user_ip' => $request->ip(),
                'session_id' => $session_id,
                'video_id' => $video->video_id,
            ]);
            $visual_sessions_count = ContentSecurityLog::where('video_id', $video->video_id)->where('type', 'visual')->count();
            $sessions_counts['visual_sessions_count'] = $visual_sessions_count;

        }
       if(!empty($sessions_counts)){
           Video::where('id', $video->id)->update($sessions_counts);
       }


        $isOwner = $user->id === $video->project()->owner || $user->id === $video->project()->team()->owner['id'];
        $isCollaborator = $video->project()->access->firstWhere('id', $user->id);

        if (!$isOwner && !$isCollaborator) {
            return response(['success' => false, 'message' => 'Access forbidden']);
        }

        $team = $request->user()->currentTeam();

        abort_unless($video && $request->user()->onTeam($team), 404);

        $comments = $video->comments;
        $comments = Comments::clearComments($comments->toTree());

        $video = $video->full();
        $video->date_formatted = $video->date_formatted;
        $experiment = Video::ifActiveExperiment($video, $request->userCookie);

        if ($experiment) {
            $video = $experiment;
        }

        /* Advanced content security */

        $video->visual_session_count = number_format(ContentSecurityLog::where('video_id', $video->video_id)->where('type', 'visual')->count());
//
//        $drm_data = doApiRequest([
//            'action' => 'get_drm_cid_license_list',
//            'startDate' => '2020-03-01',
//            'endDate' => now()->toDateString()
//        ]);
//
//        $video->drm_session_count = 0;
//
//
//        if (isset($drm_data['success']) && $drm_data['success']) {
//            foreach ($drm_data['result'] as $drm) {
//                if ($drm['cid'] == $video->video_id) {
//                    $video->drm_session_count += $drm['licenseCnt'];
//                }
//            }
//        }


        $video->drm_session_count = number_format($video->drm_session_count);

        //$video->forensic_session_count = \DB::connection('orchestrator')->table('log_data')->where('video_id', $video->video_id)->count();
        $video->forensic_session_count = number_format(0);
        $video->ip = $request->ip();
        $video->timestamp = now()->format('d/m/Y') .' - ' . now()->format('h:i:s'). ' GMT';
        $video->geoLocationDetail = $geo_location['city']. ', '. $geo_location['state']. ', ' .$geo_location['country']. ', '.$geo_location['postal_code'];
        $language = $request->server('HTTP_ACCEPT_LANGUAGE');
        $video->language = '';
        if($language !== null){
            $video->language = substr($language, 0, 2);
        }
        $details = (object)[
            'views' => $video->views,
            'leads' => $video->leads,
            'clicks' => $video->clicks
        ];

        if ($video->schedular === null) {
            unset($video['schedular']);
            $video->schedular = [
                'is_schedule' => 0,
                'stream_start_date' => null,
                'stream_start_hour' => null,
                'is_end_stream' => 0,
                'stream_start_min' => null,
                'is_stream_start_text' => 0,
                'stream_end_date' => null,
                'stream_end_hour' => null,
                'stream_end_min' => null,
                'is_action_button' => 0,
                'is_stream_end_text' => 0,
                'stream_end_text' => '',
                'button_text' => '',
                'button_link' => ''
            ];
        }

        return [
            'video' => $video,
            'comments' => $comments,
            'details' => $details
        ];
    }

    /**
     * Copy video to project
     *
     * @param Request $request
     * @return array|string
     */
    public function copyVideoToProject(Request $request)
    {
        $copy_video = Video::copyDuplicateVideo($request->all());

        return response($copy_video);
    }


    /**
     * Duplicate video to the same project
     *
     * @param Request $request
     * @return array|string
     */
    public function duplicate(Request $request)
    {
        $duplicate = Video::copyDuplicateVideo($request->all());

        return response($duplicate);
    }


    /**
     * Move video to project
     *
     * @param Request $request
     * @return array|string
     */
    public function moveVideoToProject(Request $request)
    {
        $video_id = $request->input('video_id');
        $project_id = $request->input('moved_project_id');
        $video = Video::with('videoProject')->where('id', $video_id)->first();

        if (!is_null($video)) {
            try {
                $video->project = $project_id;
                $video->save();
                return ['success' => true, 'video' => $video];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return 'error';
    }

    /**
     * get all videos and projects
     *
     * @param Request $request
     * @return array|string
     */
    public function getAllVideosAndProjects(Request $request)
    {
//      $userID = $request->input('user_id');
        $team = $request->user()->currentTeam();

        try {
            $allProjects = [];
//          $videos=Video::where('owner',$userID)->get();
            $videos = Video::where('team', $team->id)->get();
//          $projects=Project::where('owner',$userID)->get();
            $projects = Project::where('team', $team->id)->get();
            foreach ($projects as $index => $project) {
                $allProjects[$index]['id'] = $project->id;
                $allProjects[$index]['title'] = $project->title;
                $allProjects[$index]['videos_count'] = $project->video_count;

            }
            return ['videos' => $videos, 'projects' => $allProjects];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'error';
    }

    /**
     * Set Branding Logo
     *
     * @param Request $request
     * @return array|string
     */
    public function brandingLogo(Request $request)
    {
        $logo = $request->input('logo');
        $videoId = $request->input('video_id');
        $videoOptins = VideoPlayerOption::where('video_id', $videoId)->first();
        $videoOptins->branding_logo = $logo;
        $videoOptins->save();
        return $videoOptins->branding_logo;

    }

    /**
     * Copy Settings
     *
     * @param Request $request
     * @return array|string
     */
    public function copySettings(Request $request)
    {
        $videoId = $request->input('video_id');
        $checkedVideoId = $request->input('copied_video_id');
        $checked_apperance = $request->input('settings_apperance');
        $checked_thumbnail = $request->input('settings_thumbnail');
        $checked_controls = $request->input('settings_controls');
        $checked_privacy = $request->input('settings_privacy');
        $checked_before = $request->input('settings_before');
        $checked_during = $request->input('settings_during');
        $checked_after = $request->input('settings_after');


        try {
            $copyVideoOptions = VideoPlayerOption::where('video_id', $videoId)->first();
            $videoOptins = VideoPlayerOption::where('video_id', $checkedVideoId)->first();
            if ($checked_apperance) {
                $videoOptins->branding_active = $copyVideoOptions->branding_active;
                $videoOptins->color = $copyVideoOptions->color;
            }

            if ($checked_thumbnail) {
                $videoOptins->text_overlay = $copyVideoOptions->text_overlay;
                $videoOptins->thumbnail_type = $copyVideoOptions->thumbnail_type;
                $videoOptins->thumbnail_image_url = $copyVideoOptions->thumbnail_image_url;
                $videoOptins->thumbnail_video_url = $copyVideoOptions->thumbnail_video_url;
                $videoOptins->text_overlay_text = $copyVideoOptions->text_overlay_text;
            }
            if ($checked_privacy) {
                $videoOptins->permissions = $copyVideoOptions->permissions;
                $videoOptins->allow_download = $copyVideoOptions->allow_download;
                $videoOptins->embed_settings = $copyVideoOptions->embed_settings;
                $videoOptins->commenting_permissions = $copyVideoOptions->commenting_permissions;

            }
            if ($checked_controls) {
                $videoOptins->autoplay = $copyVideoOptions->autoplay;
                $videoOptins->control_visibility = $copyVideoOptions->control_visibility;
                $videoOptins->speed_control = $copyVideoOptions->speed_control;
                $videoOptins->volume_control = $copyVideoOptions->volume_control;
                $videoOptins->settings = $copyVideoOptions->settings;
                $videoOptins->share_control = $copyVideoOptions->share_control;
                $videoOptins->fullscreen_control = $copyVideoOptions->fullscreen_control;
                $videoOptins->playback = $copyVideoOptions->playback;
                $videoOptins->quality_control = $copyVideoOptions->quality_control;
                $videoOptins->chapter_control = $copyVideoOptions->chapter_control;
                $videoOptins->subtitle_control = $copyVideoOptions->subtitle_control;
            }
            if ($checked_before) {
                $videoOptins->interaction_before_email_capture = $copyVideoOptions->interaction_before_email_capture;
                $videoOptins->interaction_before_email_capture_type = $copyVideoOptions->interaction_before_email_capture_type;
                $videoOptins->interaction_before_email_capture_firstname = $copyVideoOptions->interaction_before_email_capture_firstname;
                $videoOptins->interaction_before_email_capture_lastname = $copyVideoOptions->interaction_before_email_capture_lastname;
                $videoOptins->interaction_before_email_capture_phone_number = $copyVideoOptions->interaction_before_email_capture_phone_number;
                $videoOptins->interaction_before_email_capture_allow_skip = $copyVideoOptions->interaction_before_email_capture_allow_skip;
                $videoOptins->interaction_before_email_capture_upper_text = $copyVideoOptions->interaction_before_email_capture_upper_text;


                $videoOptins->interaction_before_email_capture_lower_text = $copyVideoOptions->interaction_before_email_capture_lower_text;
                $videoOptins->interaction_before_email_capture_button_text = $copyVideoOptions->interaction_before_email_capture_button_text;
                $videoOptins->interaction_before_email_capture_email_list = $copyVideoOptions->interaction_before_email_capture_email_list;
                $videoOptins->interaction_before_email_capture_email_tags = $copyVideoOptions->interaction_before_email_capture_email_tags;

            }
            if ($checked_during) {
                $videoOptins->interaction_during_time = $copyVideoOptions->interaction_during_time;
                $videoOptins->interaction_during_active = $copyVideoOptions->interaction_during_active;
                $videoOptins->interaction_during_type = $copyVideoOptions->interaction_during_type;
                $videoOptins->interaction_during_allow_skip = $copyVideoOptions->interaction_during_allow_skip;
                $videoOptins->interaction_during_text = $copyVideoOptions->interaction_during_text;
                $videoOptins->interaction_during_image = $copyVideoOptions->interaction_during_image;
                $videoOptins->interaction_during_link_url = $copyVideoOptions->interaction_during_link_url;


                $videoOptins->interaction_during_html_code = $copyVideoOptions->interaction_during_html_code;
                $videoOptins->interaction_during_email_capture = $copyVideoOptions->interaction_during_email_capture;
                $videoOptins->interaction_during_email_capture_time = $copyVideoOptions->interaction_during_email_capture_time;
                $videoOptins->interaction_during_email_capture_type = $copyVideoOptions->interaction_during_email_capture_type;


                $videoOptins->interaction_during_email_capture_firstname = $copyVideoOptions->interaction_during_email_capture_firstname;
                $videoOptins->interaction_during_email_capture_lastname = $copyVideoOptions->interaction_during_email_capture_lastname;
                $videoOptins->interaction_during_email_capture_phone_number = $copyVideoOptions->interaction_during_email_capture_phone_number;
                $videoOptins->interaction_during_email_capture_allow_skip = $copyVideoOptions->interaction_during_email_capture_allow_skip;


                $videoOptins->interaction_during_email_capture_upper_text = $copyVideoOptions->interaction_during_email_capture_upper_text;
                $videoOptins->interaction_during_email_capture_lower_text = $copyVideoOptions->interaction_during_email_capture_lower_text;
                $videoOptins->interaction_during_email_capture_button_text = $copyVideoOptions->interaction_during_email_capture_button_text;
                $videoOptins->interaction_during_email_capture_email_list = $copyVideoOptions->interaction_during_email_capture_email_list;
                $videoOptins->interaction_during_email_capture_email_tags = $copyVideoOptions->interaction_during_email_capture_email_tags;
            }
            if ($checked_after) {
                $videoOptins->interaction_after_type = $copyVideoOptions->interaction_after_type;
                $videoOptins->interaction_after_more_videos_list = $copyVideoOptions->interaction_after_more_videos_list;
                $videoOptins->interaction_after_more_videos_text = $copyVideoOptions->interaction_after_more_videos_text;
                $videoOptins->interaction_after_cta_type = $copyVideoOptions->interaction_after_cta_type;
                $videoOptins->interaction_after_cta_html_code = $copyVideoOptions->interaction_after_cta_html_code;
                $videoOptins->interaction_after_cta_image = $copyVideoOptions->interaction_after_cta_image;
                $videoOptins->interaction_after_cta_text = $copyVideoOptions->interaction_after_cta_text;


                $videoOptins->interaction_after_cta_link_url = $copyVideoOptions->interaction_after_cta_link_url;
                $videoOptins->interaction_after_email_capture = $copyVideoOptions->interaction_after_email_capture;
                $videoOptins->interaction_after_email_capture_type = $copyVideoOptions->interaction_after_email_capture_type;
                $videoOptins->interaction_after_email_capture_firstname = $copyVideoOptions->interaction_after_email_capture_firstname;


                $videoOptins->interaction_after_email_capture_lastname = $copyVideoOptions->interaction_after_email_capture_lastname;
                $videoOptins->interaction_after_email_capture_phone_number = $copyVideoOptions->interaction_after_email_capture_phone_number;
                $videoOptins->interaction_after_email_capture_allow_skip = $copyVideoOptions->interaction_after_email_capture_allow_skip;
                $videoOptins->interaction_after_email_capture_upper_text = $copyVideoOptions->interaction_after_email_capture_upper_text;


                $videoOptins->interaction_after_email_capture_lower_text = $copyVideoOptions->interaction_after_email_capture_lower_text;
                $videoOptins->interaction_after_email_capture_button_text = $copyVideoOptions->interaction_after_email_capture_button_text;
                $videoOptins->interaction_after_email_capture_email_list = $copyVideoOptions->interaction_after_email_capture_email_list;
                $videoOptins->interaction_after_email_capture_email_tags = $copyVideoOptions->interaction_after_email_capture_email_tags;
            }
            $videoOptins->save();

            return ['player_options' => $videoOptins];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'error';
    }

    /**
     * Update video title and description
     *
     * @param Request $request
     * @return array|string
     */
    public function editTitleAndDescription(Request $request)
    {
        $videoId = $request->input('video_id');
        $videoDesrption = $request->input('video_description');
        $videoTitle = $request->input('video_title');

        try {
            if (is_numeric($videoId)) {
                $video = Video::find($videoId);
            } else {
                $video = Video::where('video_id', $videoId)->first();
                if ($video)
                    $videoId = $video->id;
            };

            $video->title = $videoTitle;
            $video->save();

            if (VideoDescription::select('id')->where('video_id', $videoId)->count() > 0) {
                $videoDesc = VideoDescription::where('video_id', $videoId)->first();
                $videoDesc->description = $videoDesrption;
                $videoDesc->save();
            } else {
                $videoDesc = new VideoDescription();
                $videoDesc->description = $videoDesrption;
                $videoDesc->video_id = $videoId;
                $videoDesc->save();
            }
            return ['message' => 'success'];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'error';
    }

    /**
     * Update video
     *
     * @param Request $request
     * @return array|string
     */
    public function update(Request $request)
    {
        $video = $request->input('video');

        try {
            if ($video['id']) {
                $team = $request->user()->currentTeam();

                $videoModel = Video::find($video['id']);
                abort_unless($videoModel && $request->user()->onTeam($team), 404);

                $videoModel->title = $video['title'];
                $videoModel->path = $video['path'];
                $videoModel->thumbnail = $video['thumbnail'];
                $videoModel->published_on_stage = ($video['published_on_stage'] ? 'true' : 'false');
                $videoModel->featured_on_stage = ($video['featured_on_stage'] ? 'true' : 'false');
                $videoModel->featured_on_stage = ($video['featured_on_stage'] ? 'true' : 'false');
                $videoModel->visual_watermarking = $video['visual_watermarking'];
                $videoModel->forensic_watermarking = $video['forensic_watermarking'];
                $videoModel->save();

                // Player options
                if (isset($video['player_options'])) {
                    //$time = $video['player_options']['interaction_during_time'];
                    //if ($time) {
                    //$video['player_options']['interaction_during_time'] = get_seconds_from_time_string($time);
                    //                    }

//                    if (isset($video['video_chapters']) && !empty($video['video_chapters'])) {
//                        $video['player_options']['chapter_control'] ='true';
//                    }

                    VideoPlayerOption::updateOptions($video['player_options'], $videoModel->id);
                }

                // Description
                if (isset($video['description']) && strlen($video['description']) > 0) {
                    $description = $video['description'];
                    $descriptionEntry = VideoDescription::firstOrNew(['video_id' => $video['id']]);
                    $descriptionEntry->video_id = $video['id'];
                    $descriptionEntry->description = $description;
                    $descriptionEntry->save();
                }


                //video_chapters

                if (isset($video['video_chapters']) && !empty($video['video_chapters'])) {


                    VideoChapter::where('video_id', $video['id'])->delete();

                    //dd($video['video_chapters']);
                    $i = 0;
                    foreach ($video['video_chapters'] as $index => $chapter) {

                        if ($chapter['title'] !== null && $chapter['time'] !== null) {

                            $time = preg_match("/^(?:[01]\d|2[0123]):(?:[012345]\d):(?:[012345]\d)$/", $chapter['time']);
                            if ($time) {

                                $video_chapter = new VideoChapter;
                                $video_chapter->video_id = $video['id'];
                                $video_chapter->title = $chapter['title'];
                                $video_chapter->time = $chapter['time'];
                                $video_chapter->index = $i;
                                $video_chapter->save();
                                $i++;
                            }
                        }

                    }


                } else {
                    VideoChapter::where('video_id', $video['id'])->delete();
                }

                if (isset($video['translated_srt_caption']) &&
                    !empty($video['translated_srt_caption']['captions']) &&
                    $video['translated_srt_caption']['type'] !== null) {
                    if($video['translated_srt_caption']['type'] == 'translated'){
                        $file = TranslatedSubtitle::with('subTitle.video')->where('id', $video['translated_srt_caption']['id'])->first();

                        $user_row = User::find($file->subTitle->video->owner);
                        $vId = $file->subTitle->video->video_id;

                    }else{
                        $file = VideoSubtitle::with('video')->where('id', $video['translated_srt_caption']['id'])->first();

                        $user_row = User::find($file->video->owner);
                        $vId = $file->video->video_id;
                    }


    //dd($video['translated_srt_caption']['caption'], $video['translated_srt_caption']->id);

                    $string = '';
                    foreach ($video['translated_srt_caption']['captions'] as $index => $c) {

                        if ($index == 0) {
                            $string .= 'WEBVTT FILE
';
                        }

                        $string .= '
' . ($index + 1) . '
' . $c['start'] . '.000 --> ' . $c['end'] . '.000
' . $c['text'] . '
';

                    }

                    file_put_contents(storage_path('data/subtitles/' . $file->stored_name), $string);
//                    $subtitles = Subtitles::load($string, 'srt');
//                    $subtitles->save(storage_path('data/subtitles/' . $file->stored_name));
                    $video = $file;
                    $user = User::getUserDetails($user_row);
                    $owner_folder = generate_owner_folder_name($user->owner->email);

                    $file_key = "{$owner_folder}/{$vId}/subtitles/{$file->stored_name}";


                    $endpoint = config('aws.endpoint');
                    $bucket = config('aws.bucket');

                    $s3 = S3Client::factory(array(
                        'endpoint' => $endpoint,
                        'region' => config('aws.region'),
                        'version' => config('aws.version'),
                        'credentials' => config('aws.credentials')
                    ));

                    try {
                        $s3->putObject(array(
                            'Bucket' => $bucket,
                            'Key' => $file_key,
                            'Body' => file_get_contents(storage_path('data/subtitles/' . $file->stored_name)),
//                'ContentLength' => $file_size,
                            'ACL' => 'public-read'
                        ));

                        $file_path = $s3->getObjectUrl($bucket, $file_key);


                        $file->update(['url' => $file_path, 'status' => 1]);
                    } catch (\Exception $exception) {
                        $file->update(['url' => $file_path, 'status' => 2]);
                    }
                }


                if (isset($video['video_basecodes'])) {

                    $basecode = VideoBasecode::updateOrCreate([
                        'video_id' => $video['id'],

                    ], [
                        'code' => $video['video_basecodes']['code'],
                        'name' => 'custom',

                    ]);

//                    dd($video['video_basecodes']['code']);
                    VideoEventcode::where('basecode_id', $basecode->id)->delete();

                    foreach ($video['video_basecodes']['event_code'] as $index => $code){

                        $eventcode = new VideoEventcode;
                            $eventcode->code  = $code['code'];
                            $eventcode->basecode_id  = $basecode->id;
                            $eventcode->time  = $code['time'];
                            $eventcode->name  = 'custom';
                            $eventcode->save();

                    }

//                    $videoModel->video_basecodes = $video['video_basecodes'];

                    $v = Video::with('videoBasecode.eventCode')->where('id', $video['id'])->first();
                    $videoModel->video_basecodes = $v->videoBasecode;
//                    $videoModel->video_eventcodes = $v->videoBasecode !== null ? $v->videoBasecode->eventCode : [];
                }


                $videoModel->player_options = (isset($playerOptionsModel) ? $playerOptionsModel : []);
                $videoModel->description = (isset($descriptionEntry) ? $descriptionEntry->description : '');
                if (isset($video['schedular']) && $video['schedular'] != null) {
                    if (isset($video['schedular']['is_running'])) {
                        unset($video['schedular']['is_running']);
                    }
                    if (isset($video['schedular']['is_stream_ended'])) {
                        unset($video['schedular']['is_stream_ended']);
                    }

                    $videopublish = VideoPublishSchedular::where('video_id', $video['id'])->first();
                    $schedularData = $this->videoSchedularData($video['schedular']);
                    if ($videopublish == null && $video['schedular'] != null) {
                        $schedularData['video_id'] = $video['id'];
                        if ($schedularData['is_schedule']) {
                            VideoPublishSchedular::insert($schedularData);
                        }
                    } else {
                        if ($schedularData['is_schedule']) {
                            VideoPublishSchedular::where('video_id', $video['id'])->update($schedularData);
                        } else {
                            VideoPublishSchedular::where('video_id', $video['id'])->delete();
                        }
                    }
                }
                return [
                    'video' => $videoModel,
                ];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'error';
    }

    /**
     * Creates a new thumbnail
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function createThumbnail(Request $request)
    {
        $videoId = $request->get('video_id');
        $time = $request->get('time');
        $video = Video::find($videoId);
        if ($video) {
            $thumbnailPath = Thumbnail::generate($video, $time);

            $thumbnail = new VideoRelevantThumbnail();
            $thumbnail->key = $video->video_id;
            $thumbnail->url = $thumbnailPath;
            $thumbnail->save();

            return [
                'url' => $thumbnail->url,
                'small' => \Bkwld\Croppa\Facade::url($thumbnail->url, 120, 70)
            ];
        }
        throw new \Exception('Video was not found.');
    }

    public function destroy(Request $request)
    {
        $vc = new VideoController();
        return $vc->destroy($request);
    }

    public function deleteVideo(Request $request)
    {
        $video_id = $request->input('video_id');

        if (isset($video_id) && !is_null($video_id)) {
            if (is_numeric($video_id)) {
                $video = Video::with('videoProject', 'snapPages')->where(['id' => $video_id])->first();
            } else {
                $video = Video::with('videoProject', 'snapPages')->where(['video_id' => $video_id])->first();
            }

            if (!is_null($video)) {
                $project_id = $video->videoProject->project_id;
                $delete = Video::deleteAllBucketFiles($video->video_id, $video->owner);

                if (isset($delete['success']) && $delete['success'] == true) {
                    /* try to delete video record with their relations */
                    try {
                        $video->delete();

                        if (count($video->snapPages) > 0) {
                            $result = SnapPage::deleteMultipleSnapPage($video->snapPages);
                            if ($result == 'success') {
                                return ['success' => true, 'project_id' => $project_id, 'message' => 'Your video deleted successfully.'];
                            }
                        }

                        return ['success' => true, 'project_id' => $project_id, 'message' => 'Your video deleted successfully.'];
                    } catch (\Exception $exception) {
                        return ['success' => false, 'message' => $exception->getMessage(), $exception->getCode()];
                    }
                }
                return $delete;
            }
            return ['success' => false, 'message' => 'There is no video with the requested unique id'];
        }
        return ['success' => false, 'message' => 'You must send video unique id'];
    }

    function videoSchedularData($data)
    {
        if ($data != null) {
            if (isset($data['is_schedule'])) {
                $data['stream_start_date'] = date('Y-m-d', strtotime('+1 days ' . $data['stream_start_date']));

            }
            if (isset($data['is_end_stream']) && $data['stream_end_date'] != '') {
                $data['stream_end_date'] = date('Y-m-d', strtotime('+1 days ' . $data['stream_end_date']));

            }
        }
        return $data;
    }

}
