<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ExtractAudioFromVideo;
use App\TranslatedSubtitle;
use App\VideoBasecode;
use App\VideoEventcode;
use App\VideoFingerprint;
use App\VideoSubtitle;
//use Benlipp\SrtParser\Parser;
use Done\Subtitles\Subtitles;
use Google\Cloud\Translate\V2\TranslateClient;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TusPhp\Events\TusEvent;
use TusPhp\Tus\Server as TusServer;
use App\CtaElement;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessVideo;
use App\Jobs\UploadVIdeoToWasabi;
use App\Project;
use App\Repositories\Project\ProjectRepository;
use App\SharedSnap;
use App\User;
use App\Video;
use App\VideoRelevantThumbnail;
use App\VideoThumbnailScrumb;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Statistic;
use App\VideoPlayerOption;
use App\UserSettings;
use Podlove\Webvtt\Parser;
use Podlove\Webvtt\ParserException;
use App\VideoWatchSession;
use App\SnapPage;

class VideoController extends Controller
{
    public function all(Request $request)
    {
        $projectRepository = new ProjectRepository(new Project());
        $user = Auth::user();
        $projects = collect($projectRepository->involvedProjectsByUserIdThatHaveVideos($user->id));

        $options = $projects->map(function ($project) {
            $index = [];
            $index['label'] = $project->title;

            $index['options'] = $project->videos->map(function ($video) use ($project) {
                return [
                    'label' => $video->title,
                    'value' => $video->id,
                    'thumbnail' => $video->thumbnail,
                    'project_id' => $project->id,
                    'project_title' => $project->title,
                    'video_id' => $video->id,
                    'video_title' => $video->title
                ];
            });
            return $index;
        });

        return $options;
    }

    /**
     * Returns one video
     *
     * @param Request $request
     * @return array
     */
    public function show(Request $request)
    {
        $id = $request->input('id');
        $ip = $request->getClientIp();
        $geo_location = geoip()->getLocation($ip);
        $cookie = $request->userCookie;

        $user_country = $geo_location['iso_code'];
        $video = Video::with('schedular')->where('id', $id)->orWhere('video_id', $id)->first();
        if (is_numeric($id))
            $video = Video::with('schedular')->find($id);
        else
            $video = Video::with('schedular')->where('video_id', $id)->first();

        $abort = false;
        if (isset($video)) {

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

            // Abort if video is private and user not on team
            $project = $video->project();
            if (@$project->private == 1) {
                $team = $project->team();
                if (!$request->user()->onTeam($team))
                    $abort = true;
            }
        }

        abort_unless(isset($video) && $abort == false, 404);
        $experiment = Video::ifActiveExperiment($video, $cookie);
        if ($experiment) {
            $video = $experiment;
        }
        $video_detail = $video->full();


        $forensic_detail = Cache::get('forensic_file_' . $video->video_id . '_' . $request->ip(), false);

        if ($forensic_detail && $video_detail->forensic_watermarking == 1) {

            if ($video->drm_protection == 1) {

                if ($forensic_detail['protocol'] === 'hls') {
                    $video_detail->hls_url_aes = $forensic_detail['url'];
                } else {
                    $video_detail->dash_url_drm = $forensic_detail['url'];
                }
            } else {
                $video_detail->path = $video_detail->src = $forensic_detail['url'];

            }
            $video_detail->forensic_session_id = $forensic_detail['forensic_session_id'];

            session()->forget('forensic_file');
        }

        $setting = UserSettings::where('user_id', $video_detail->owner)->first();
        $countries = [];
        if ($setting->restricted_countries !== null) {
            $restricted_countries = json_decode($setting->restricted_countries, true);

            foreach ($restricted_countries as $country) {
                foreach ($country as $c) {
                    array_push($countries, $c);
                }
            }
        }


//dd($setting, $video_detail->owner, $countries);
        $video_detail->allow_video = !in_array($user_country, $countries);
        $video_detail->ip = $request->ip();
        $video_detail->timestamp = now()->format('d/m/Y') . ' - ' . now()->format('h:i:s') . ' GMT';
        $video_detail->geoLocationDetail = $geo_location['city']. ', '. $geo_location['state']. ', ' .$geo_location['country']. ', '.$geo_location['postal_code'];
        $language = $request->server('HTTP_ACCEPT_LANGUAGE');
        $video->language = '';
        if($language !== null){
            $video->language = substr($language, 0, 2);
        }

        return [
            'video' => $video_detail
        ];
    }

    public function get_list(Request $request)
    {
        $videoIds = json_decode($request->input('ids'));

        return collect($videoIds)->map(function ($id) {
            $video = Video::find($id);
            if ($video)
                return $video->full();
        })->filter(function ($value, $key) {
            return $value != null;
        })->toArray();
    }

    /**
     * Unlock a video via password
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector|void
     */
    public function unlock(Request $request)
    {
        $id = $request->input('id');
        $password = $request->input('password');

        $video = [];
        $video = Video::where(['video_id' => $id])->orWhere(['id' => $id])->first();
        abort_unless($video, 404);

        $playerOptions = $video->player_options->toArray();

        // Check password
        if ($playerOptions['password'] == $password) {
            // show video
            return $this->show($request);
        }

        abort(403);
    }

    /**
     * Creating a new video
     *
     * @param Request $request
     * @return Video|array
     */
    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $projectId = $request->input('project_id');
        $project = Project::findOrFail($projectId);
        $title = $request->input('title', $request->input('upload_custom_name', $request->input('upload_original_name', 'Untitled')));
        $filename = $request->input('upload_custom_name', $request->input('upload_original_name', 'imported'));

        abort_unless($request->user()->onTeam($project->team()), 404);

        // Set video variables
        $video = new Video();
        $video->video_id = str_random('8');
        $video->title = str_replace($userId . '_original_', '', $title);
        $video->project = $projectId;
        $video->owner = $request->user()->id;
        $video->team = $project->team;
        $video->filename = $filename;
        $video->thumbnail = '';
        $video->path = $request->input('path', '');
        $video->duration = (int)$request->input('duration', 0);
        $video->duration_formatted = $request->input('duration_formatted', '');
        $video->save();

        // Dispatch processing video job
        dispatch(new ProcessVideo($video));

        return $video;
    }

    /**
     * Destroys a resource
     *
     * @param Request $request
     * @return string
     */
    public function destroy(Request $request)
    {
        $video = Video::findOrFail($request->input('id'));

        abort_unless($request->user()->onTeam($video->team()), 404);

        try {
            // TODO: what if thumbnails are in subdomain? We should send a command to VideoAnt
            // Delete thumbnails
            $videoIdThumbnailPath = env('VIDEOANT_URL') . '/thumbnails/' . $video->video_id;
            if (\File::isDirectory($videoIdThumbnailPath)) {
                \File::deleteDirectory($videoIdThumbnailPath);
            }

            // Delete thumbnail scrumb entry in this directory
            $scrumbPath = $videoIdThumbnailPath . '/scrumb.jpg';
            VideoThumbnailScrumb::whereThumbnailScrumb($scrumbPath)->delete();

            // Delete relevant thumbnails
            VideoRelevantThumbnail::whereKey($video->video_id)->delete();

            $video->delete();

        } catch (\Exception $e) {
            return false;
        }

        return 'success';
    }

    /**
     * Duplicates a video
     *
     * @param Request $request
     * @return mixed
     */
    public function duplicate(Request $request)
    {
        // Duplicate a video
        $videoId = $request->input('id');
        $projectId = $request->input('project_id', 0);

        $video = Video::find($videoId);

        abort_unless($request->user()->onTeam($video->team()), 404);

        if ($video->exists())
            return $video->duplicate($projectId);
    }

    /**
     * Moves a video
     *
     * @param Request $request
     * @return mixed
     */
    public function move(Request $request)
    {
        $videoId = $request->input('id');
        $newProjectId = $request->input('project_id');

        $video = Video::find($videoId);

        abort_unless($request->user()->onTeam($video->team()), 404);

        if (($video) > 0) {
            $video->project = $newProjectId;
            $video->save();
        }
        return $video;
    }

    /**
     * Creates a video instance and returns video_id
     *
     * @param Request $request
     * @return string
     */
    public function before_upload(Request $request)
    {
        \Log::info(json_encode($request->all()));
        // allow user id parameter in debug mode
        //if (env('APP_DEBUG') && $request->input('user_id'))
        //    $userId = $request->input('user_id');
        //else
        //    $userId = auth()->user()->id;
        $userId = $request->input('user_id');
        $user = \App\User::findOrFail($userId);

        $projectId = $request->input('project_id');
        $project = $project = Project::findOrFail($projectId);

        abort_unless($user->onTeam($project->team()), 404);

        $title = $request->input('title', $request->input('filename', 'Untitled'));
        $filename = $request->input('filename');

        // Set video variables
        $video = new Video();
        $video->video_id = str_random('8');
        $video->title = $title;
        $video->project = $projectId;
        $video->owner = $userId;
        $video->team = $project->team;
        $video->filename = $filename;
        $video->thumbnail = '';
        $video->save();

        return $video->video_id;
    }

    public function transcoding_progress_report(Request $request)
    {
        \Log::info(json_encode($request->all()) . ' transcoding_progress_report');
        $videoId = $request->input('video_id', $request->input('id'));
        $video = \App\Video::where(['video_id' => $videoId])->first();
        $video->transcoding_progress = (int)$request->input('progress');
        if ($video->update())
            return 'success';
        abort(500);
    }

    /**
     * probably deprecated
     **/
    public function success(Request $request)
    {
        // ["http:\/\/s3.eu-central-1.wasabisys.com\/mmvs\/N5v2bGCX.mov-360p.mp4","http:\/\/s3.eu-central-1.wasabisys.com\/mmvs\/N5v2bGCX.mov-360p.mp4","http:\/\/s3.eu-central-1.wasabisys.com\/mmvs\/N5v2bGCX.mov-01.png","http:\/\/s3.eu-central-1.wasabisys.com\/mmvs\/N5v2bGCX.mov-02.png","http:\/\/s3.eu-central-1.wasabisys.com\/mmvs\/N5v2bGCX.mov-03.png","http:\/\/s3.eu-central-1.wasabisys.com\/mmvs\/N5v2bGCX.mov-04.png"]
        \Log::info(json_encode($request->all()) . ' Api/VideoController@success');

        // Separate thumbnail files from video files
        $all_files = collect($request->input('list_url'));
        $video_files = $all_files->filter(function ($value, $key) {
            return !ends_with($value, '.png') && !ends_with($value, '.jpg') && !ends_with($value, '.jpeg');
        })->unique();
        $path = $video_files->filter(function ($value) {
            return (strpos($value, '360p') !== false);
        })->first();
        $thumbnail_files = $all_files->filter(function ($value, $key) {
            return (ends_with($value, '.png') || ends_with($value, '.jpg') || ends_with($value, '.jpeg'));
        })->unique();

        $videoId = $request->input('video_id', $request->input('id'));
        $video = \App\Video::where(['video_id' => $videoId])->first();
        $video->files = json_encode($video_files->toArray());
        $video->path = $path;
        $video->thumbnails = json_encode($thumbnail_files->toArray());
        $video->thumbnail = $thumbnail_files->nth(2)->first();
        $video->transcoding_size_source = $request->input('size_source');
        $video->transcoding_size_out = $request->input('size_out');
        $video->transcoding_price = $request->input('price');
        $video->transcoding_badnwidth = $request->input('bandwidth');
        $video->transcoding_duration = $request->input('duration');
        // $video->duration = $request->input('duration');
        // $video->duration_formatted = format_duration($request->input('duration'));
        if ($video->update())
            return $video;
        abort(500);
    }


    public function chunkResumableUpload(Request $req, $project_id, $upload_key = null)
    {
        $server = new TusServer('redis');
        $temp_upload_path = storage_path('uploads');

        if (!file_exists($temp_upload_path)) {
            mkdir($temp_upload_path, 0777);
        }

        $server->setUploadDir($temp_upload_path);

        if ($upload_key !== null) {
            $server->setUploadKey($upload_key);
        }

        $server->setApiPath(route('upload.video', ['project_id' => $project_id], false));

        $server->event()->addListener('tus-server.upload.complete', function (TusEvent $event) use ($project_id, $req) {

            try {
                $project = Project::findOrFail($project_id);
                $user_row = auth()->user();

                $fileMeta = $event->getFile()->details();

                $drm_protection = isset($fileMeta['metadata']['drm_protection']) && $fileMeta['metadata']['drm_protection'] === 'true';

                $file_name = $fileMeta['name'] ?? 'Video';
                $file_name = substr($file_name, 0, strrpos($file_name, "."));

                $duration = $fileMeta['metadata']['duration'] ?? 0;
                $duration_formatted = $fileMeta['metadata']['duration_formatted'] ?? '';

                $video = new Video();
                $video->video_id = generate_video_unique_id();
                $video->title = $file_name;
                $video->project = $project->id;
                $video->owner = $user_row->id;
                $video->team = $project->team;
                $video->filename = $fileMeta['name'];
                $video->thumbnail = '';
                $video->path = '';
                $video->files = '';
                $video->duration = $duration;
                $video->duration_formatted = $duration_formatted;
                $video->transcoding_progress = 100;
                $video->transcoding_size_source = $fileMeta['size'];
                $video->transcoding_size_out = '280449559';
                $video->transcoding_price = '0.0';
                $video->transcoding_badnwidth = 0;
                $video->published_on_stage = false;
                $video->transcoding_duration = $duration;
                $video->save();

                UploadVIdeoToWasabi::dispatch($video, $fileMeta['file_path'], $drm_protection);
                addToLog(['user_id' => $user_row->id,
                    'activity_type' => 'video_upload',
                    'subject' => "Uploaded a video: <span class='activity-text'>$video->filename</span>"
                ]);
            } catch (\Exception $exception) {

                throw new \Exception(json_encode([
                    'status' => false,
                    'message' => $exception->getMessage()
                ]));
            }
        });

        $response = $server->serve();
        $response->send();
    }


    public function chunkResumableReplace(Request $request, $video_id, $upload_key = null)
    {
        $video = Video::where(['video_id' => $video_id])->firstOrFail();
        $replace_type = $request->input('replace_type');

        if (isset($replace_type) && $replace_type == 'archived') {
            Video::deleteAllStatistics($video->id);
        }

        Video::deleteAllBucketFiles($video->video_id, $video->owner);

        $server = new TusServer('redis');
        $temp_upload_path = storage_path('uploads');

        if (!file_exists($temp_upload_path)) {
            mkdir($temp_upload_path, 0777);
        }

        $server->setUploadDir($temp_upload_path);

        if ($upload_key !== null) {
            $server->setUploadKey($upload_key);
        }

        $server->setApiPath(route('video.chunk.replace', ['video_id' => $video_id], false));

        $server->event()->addListener('tus-server.upload.complete', function (TusEvent $event) use ($video) {

            try {
                $fileMeta = $event->getFile()->details();

                UploadVideoToWasabi::dispatch($video, $fileMeta['file_path'], $video->drm_protection);
            } catch (\Exception $exception) {

                throw new \Exception([
                    'status' => false,
                    'message' => $exception->getMessage()
                ]);
            }
        });

        $response = $server->serve();
        $response->send();
    }


    public function xhrUpload(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        $snap = $request->snap;
        $title = $request->snapTitle;
        $description = $request->snapDesc;
        $type = $request->type;

        if ($request->file('video')) {
            $file = $_FILES['video'];

            if (!$request->owner) {
                $user_row = auth()->user();
                $user = User::getUserDetails($user_row);
            } else {
                $user_row = User::find($request->owner);
                $user = User::getUserDetails($user_row);
            }

            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_error = $file['error'];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            if ($file_error === 0) {
                $drm_protection = isset($request->drm_protection) && $request->drm_protection === "true" ? true : false;

                $owner_folder = generate_owner_folder_name($user->owner->email);
                $video_id = generate_video_unique_id();

                try {
                    $file_key = "{$owner_folder}/{$video_id}/{$video_id}.{$file_ext}";

                    $put_object = Video::uploadFileToBucket($file_key, $file_tmp, $file_size);
                    if (isset($put_object['success']) && $put_object['success'] == true && isset($put_object['file_path'])) {
                        $file_path = $put_object['file_path'];

                        $video = new Video();
                        $video->video_id = $video_id;
                        $video->title = $file_name ?? 'video';
                        $video->project = $project->id;
                        $video->owner = auth()->user()->id;
                        $video->team = $project->team;
                        $video->filename = $file_name . '.' . $file_ext;
                        $video->thumbnail = '';
                        $video->path = $file_path;
                        $video->files = '';
                        $video->duration = (int)$request->input('duration', 0);
                        $video->duration_formatted = $request->input('duration_formatted', '');
                        $video->duration = 0;
                        $video->duration_formatted = 0;
                        $video->transcoding_progress = 100;
                        $video->transcoding_size_source = $file_size;
                        $video->transcoding_size_out = '280449559';
                        $video->transcoding_price = '0.0';
                        $video->transcoding_badnwidth = 0;
                        $video->published_on_stage = false;
                        $video->transcoding_duration = (int)$request->input('duration', 0);

                        if ($video->save()) {

                            if ($description) {
                                $video->videoDescription()->create([
                                    'description' => $description
                                ]);
                            }
                            if ($type && $type == 'shared') {
                                $sharedSnap = SharedSnap::find($request->shared_snap_id);
                                if ($sharedSnap) {
                                    if ($sharedSnap->completed == 0 || !$sharedSnap->completed) {
                                        $sharedSnap->update([
                                            'video_id' => $video->id,
                                            'creator_name' => $request->creator_name,
                                            'creator_email' => $request->creator_email,
                                            'completed' => 1
                                        ]);
                                    } else {
                                        SharedSnap::create([
                                            'project_id' => $sharedSnap->project_id,
                                            'snap_label_id' => $sharedSnap->snap_label_id,
                                            'video_id' => $video->id,
                                            'creator_name' => $request->creator_name,
                                            'creator_email' => $request->creator_email,
                                            'completed' => 1
                                        ]);
                                    }
                                }
                            }
                            $data = array(
                                'action' => 'add',
                                'input' => array(
                                    'url' => trim($file_path),
                                    'video_id' => $video_id,
                                    'account_folder' => $owner_folder,
                                    'drm' => $drm_protection,
                                    'callback' => config('env.ROOT_URL') . '/callback-video'
                                ),
                                'output' => array(
                                    'output' => 'wasabi',
                                    "gif" => [
                                        "timestamp" => [5]
                                    ],

                                ),
                            );

                            try {
                                $encode_request = doApiRequest($data);
                            } catch (\Exception $exception) {
                                return response(['success' => false, 'message' => $exception->getMessage()], $exception->getCode());
                            }

                            return response([
                                'success' => true,
                                'message' => 'file saved successfully',
                                'video' => $video,
                                'encoding' => $encode_request
                            ], 200);
                        }
                    }

                    return response(['success' => false, 'message' => isset($put_object['message']) ? $put_object['message'] : 'Video does not uploaded']);

                } catch (\Exception $exception) {
                    return response(['success' => false, 'message' => $exception->getMessage()], $exception->getCode());
                }
            }
            return response(['message' => 'fail', 'error' => $file_error], 200);
        }

        return abort(400);
    }

    public function getDrmKeys()
    {
        $data = array(
            'action' => 'get_drm_key',
            'username' => 'precious'
        );

        $response = doApiRequest($data);

        if ($response['success']) {
            $drmKeys = $response['result'];
            return response([
                'success' => true,
                'drmKeys' => $drmKeys,
                'url' => 'https://encoding.bigcommand.com/api/v1.0/api.php'
            ], 200);
        }

        return response(['success' => false]);
    }

    public function uploadSRTfile(Request $request)
    {

        $new_name = '';
        if ($request->has('file')) {
            $file = $request->file('file');
            if ($file !== null) {
                $new_name = 'temp_' . strtolower(Str::random(32)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('/subtitles', $new_name);

            }
        }
        return response()->json(['filename' => $new_name]);
    }

    public function store_subtitle_detail(Request $request)
    {

        $new_file_name = str_replace('temp_', '', $request->file);

        $subtitle = VideoSubtitle::updateOrCreate([
            'video_id' => $request->vid,
        ], [
            'language' => $request->language,
            'stored_name' => $new_file_name,
            'lang_name' => $request->lang_name,
            'status' => 0,
        ]);

        $video = $subtitle->with('video')->first();
        $user_row = User::find($video->video->owner);
        $user = User::getUserDetails($user_row);
        $owner_folder = generate_owner_folder_name($user->owner->email);
        $file_key = "{$owner_folder}/{$video->video->video_id}/subtitles/{$new_file_name}";


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
                'Body' => file_get_contents(storage_path('data/subtitles/' . $request->file)),
//                'ContentLength' => $file_size,
                'ACL' => 'public-read'
            ));

            $file_path = $s3->getObjectUrl($bucket, $file_key);


            $video->update(['url' => $file_path, 'status' => 1]);
        } catch (\Exception $exception) {
            $video->update(['url' => $file_path, 'status' => 2]);
        }


        rename(storage_path('data/subtitles/' . $request->file), storage_path('data/subtitles/' . $new_file_name));

        return response()->json(['subtitle' => $subtitle]);
    }

    public function translateSrt(Request $request)
    {
        $subtitle = VideoSubtitle::where('id', $request->subtitle_id)->first();



        $exist = TranslatedSubtitle::where('subtitle_id', $request->subtitle_id)->where('language', $request->language)->exists();

        if(!$exist){

            $new_file_name = $request->language . '_' . $subtitle->stored_name;
            $translate = new TranslateClient([
                'key' => 'AIzaSyCHCdBzYC91SzQoR7iwgMu2jGJPFizdWCk'
            ]);

            $parser = new Parser();
//        $content1 = "WEBVTT\n\n00:00:00.000 --> 01:22:33.440\nHello world\n\n01:22:33.440 --> 01:22:34.440\n<v Eric>Hi again\n";
            $content = file_get_contents(storage_path('data/subtitles/' . $subtitle->stored_name));
            $captions = $parser->parse($content);

            $captions = $captions['cues'];

            $string = "";
            foreach ($captions as $index => $caption) {

                $start = explode('.', $caption['start']);
                $end = explode('.', $caption['end']);
                $start_milliseconnds = '.000';
                if (isset($start[1])) {
                    $len = strlen($start[1]);
                    if ($len == 3) {
                        $start_milliseconnds = '.' . $start[1];
                    } else {
                        if ($len == 2) {
                            $start_milliseconnds = '.' . $start[1] . '0';
                        } else {
                            $start_milliseconnds = '.' . $start[1] . '00';
                        }
                    }
                }


                $end_milliseconnds = '.000';
                if (isset($end[1])) {
                    $len = strlen($end[1]);
                    if ($len == 3) {
                        $end_milliseconnds = '.' . $end[1];
                    } else {
                        if ($len == 2) {
                            $end_milliseconnds = '.' . $end[1] . '0';
                        } else {
                            $end_milliseconnds = '.' . $end[1] . '00';
                        }
                    }
                }

                $result = $translate->translate($caption['text'], [
                    'target' => $request->language
                ]);

                if ($index == 0) {
                    $string .= 'WEBVTT FILE
';
                }

                $string .= '
' . $caption['identifier'] . '
' . gmdate("H:i:s", $start[0]) . $end_milliseconnds . ' --> ' . gmdate("H:i:s", $end[0]) . $end_milliseconnds . '
' . $result['text'] . '
';

            }

            file_put_contents(storage_path('data/subtitles/' . $new_file_name), $string);

            $data = TranslatedSubtitle::updateOrCreate([
                'subtitle_id' => $request->subtitle_id,
                'language' => $request->language,
            ], [
                'stored_name' => $new_file_name,
                'lang_name' => $request->lang_name,
                'status' => 0
            ]);

//        $video = $subtitle->with('video')->first();
            $video = VideoSubtitle::with('video')->where('id', $subtitle->id)->first();
            $user_row = User::find($video->video->owner);
            $user = User::getUserDetails($user_row);
            $owner_folder = generate_owner_folder_name($user->owner->email);
            $file_key = "{$owner_folder}/{$video->video->video_id}/subtitles/{$new_file_name}";


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
                    'Body' => file_get_contents(storage_path('data/subtitles/' . $new_file_name)),
//                'ContentLength' => $file_size,
                    'ACL' => 'public-read'
                ));

                $file_path = $s3->getObjectUrl($bucket, $file_key);


                $data->update(['url' => $file_path, 'status' => 1]);
                if($data['status'] == 1){
                    $video = Video::where('id', $subtitle->video_id)->first();
                    $minutes = (int) round(ceil($video->duration/60));
                    if($video->translation_minutes !== null){
                        $minutes = $minutes + ($video->translation_minutes);
                    }
                    $video->update(['translation_minutes' => $minutes]);

                }



            } catch (\Exception $exception) {
                $data->update(['url' => $file_path, 'status' => 2]);
            }

        }else{
            $data = TranslatedSubtitle::where('subtitle_id', $request->subtitle_id)->where('language', $request->language)->first();
        }

        return response()->json(['data' => $data, 'exist' => $exist]);
    }

    public function reomveTranslatedSubtitle(Request $request)
    {

        TranslatedSubtitle::where('id', $request->id)->delete();
        return response()->json(true);

    }

    public function editTranslatedSubtitle(Request $request)
    {
        if($request->type == "translated"){

            $file = TranslatedSubtitle::where('id', $request->id)->first();
        }else{
            $file = VideoSubtitle::where('id', $request->id)->first();
        }

        $file_name = $file->stored_name;
//      $file_name = explode('_', $file->stored_name);
        $parser = new Parser();
//        $parser->loadFile(storage_path('data/subtitles/' . $file_name));
        $content = file_get_contents(storage_path('data/subtitles/' . $file_name));
        $captions = $parser->parse($content);

        $captions = $captions['cues'];

        foreach ($captions as $index => $caption) {
            $start = explode('.', $caption['start']);
            $end = explode('.', $caption['end']);
            $captions[$index]['start'] = gmdate("H:i:s", $start[0]);
            $captions[$index]['end'] = gmdate("H:i:s", $end[0]);
//            dd(gmdate("H:i:s", $start[0]), $caption);

        }

        return response()->json(['captions' => $captions, 'data' => $file, 'type' => $request->type]);
    }

    public function uploadTranslatedSrtFileToEdit(Request $request)
    {

        $oldFile = TranslatedSubtitle::where('id', $request->id)->first();
//        dd($request->id);
        if ($oldFile !== null) {
            rename(storage_path('data/subtitles/' . $request->file), storage_path('data/subtitles/' . $oldFile->stored_name));

            $translate = new TranslateClient([
                'key' => 'AIzaSyCHCdBzYC91SzQoR7iwgMu2jGJPFizdWCk'
            ]);

            $parser = new Parser();
//            $parser->loadFile(storage_path('data/subtitles/' . $oldFile->stored_name));
//            $captions = $parser->parse();

            $content = file_get_contents(storage_path('data/subtitles/' . $oldFile->stored_name));
            $captions = $parser->parse($content);
            $captions = $captions['cues'];

            $result = $translate->detectLanguage($captions[0]['text']);

            if ($result['languageCode'] !== $request->language) {
                $string = "";
                foreach ($captions as $index => $caption) {

                    $start = explode('.', $caption['start']);
                    $end = explode('.', $caption['end']);
                    $start_milliseconnds = '.000';
                    if (isset($start[1])) {
                        $len = strlen($start[1]);
                        if ($len == 3) {
                            $start_milliseconnds = '.' . $start[1];
                        } else {
                            if ($len == 2) {
                                $start_milliseconnds = '.' . $start[1] . '0';
                            } else {
                                $start_milliseconnds = '.' . $start[1] . '00';
                            }
                        }
                    }
                    $end_milliseconnds = '.000';
                    if (isset($end[1])) {
                        $len = strlen($end[1]);
                        if ($len == 3) {
                            $end_milliseconnds = '.' . $end[1];
                        } else {
                            if ($len == 2) {
                                $end_milliseconnds = '.' . $end[1] . '0';
                            } else {
                                $end_milliseconnds = '.' . $end[1] . '00';
                            }
                        }
                    }

                    $result = $translate->translate($caption['text'], [
                        'target' => $request->language
                    ]);


                    if ($index == 0) {
                        $string .= 'WEBVTT FILE
';
                    }

                    $string .= '
' . $caption['identifier'] . '
' . gmdate("H:i:s", $start[0]) . $end_milliseconnds . ' --> ' . gmdate("H:i:s", $end[0]) . $end_milliseconnds . '
' . $result['text'] . '
';


//                    $string .= '
//
//' . ($index + 1) . '
//' . gmdate("H:i:s", $start[0]) . $end_milliseconnds . ' --> ' . gmdate("H:i:s", $end[0]) . $end_milliseconnds . '
//' . $result['text'];

                }
//                $subtitles = Subtitles::load($string, 'srt');
//                $subtitles->save(storage_path('data/subtitles/' . $oldFile->stored_name));
                file_put_contents(storage_path('data/subtitles/' . $oldFile->stored_name), $string);

//                $parser->loadFile(storage_path('data/subtitles/' . $oldFile->stored_name));
//                $captions = $parser->parse();
                $content = file_get_contents(storage_path('data/subtitles/' . $oldFile->stored_name));
                $captions = $parser->parse($content);
                $captions = $captions['cues'];
            }


            foreach ($captions as $index => $caption) {
                $start = explode('.', $caption['start']);
                $end = explode('.', $caption['end']);
                $captions[$index]['start'] = gmdate("H:i:s", $start[0]);
                $captions[$index]['end'] = gmdate("H:i:s", $end[0]);

            }


        }
        return response()->json(['captions' => $captions]);


    }

    public function generateAndDownloadSrt(Request $request)
    {
        $captions = $request->captions;
        $string = "";
        foreach ($captions as $index => $caption) {


            if ($index == 0) {
                $string .= 'WEBVTT FILE
';
            }


            $string .= '
' . $caption['identifier'] . '
' . $caption['start'] . ',000 --> ' . $caption['end'] . ',000
' . $caption['text'] . '
';


//            $string .= '
//
//' . ($index + 1) . '
//' . $caption['startTime'] . ',000 --> ' . $caption['endTime'] . ',000
//' . $caption['text'];

        }

//        $tmp_filename = 'temp_' . strtolower(Str::random(32)) . '.srt';
//        $subtitles = Subtitles::load($string, 'srt');
//        $subtitles->save(storage_path('data/subtitles/' . $tmp_filename));
        $tmp_filename = 'temp_' . strtolower(Str::random(32)) . '.vtt';
        file_put_contents(storage_path('data/subtitles/' . $tmp_filename), $string);

        $headers = ['Content-Type: text/vtt'];
        $url = route('downloadSrt', ['name' => $tmp_filename]);

        return response()->json(['url' => $url]);


    }

    public function DownloadSrt(Request $request, $name)
    {

        $file_path = storage_path('data/subtitles/' . $name);
        return response()->download($file_path, 'download.vtt');

    }

    public function generateSrtFromVideo(Request $request)
    {

        $video = Video::where('id', $request->vid)->first();

        $job_id = $video->id . '_' . Str::random(32);

        $new_file_name = strtolower(Str::random(32)) . '.vtt';

        $subtitle = VideoSubtitle::updateOrCreate([
            'video_id' => $video->id,
        ], [
            'language' => $request->language,
            'stored_name' => $new_file_name,
            'lang_name' => $request->lang_name,
            'status' => 0,
            'sub_status' => 'pending',
            'job_id' => $job_id,
        ]);
        $subtitle = VideoSubtitle::where('id', $subtitle->id)->first();

        Cache::forever($job_id, 'pending');

        if($video->audio_url !== null){
            ExtractAudioFromVideo::dispatch($video, $request->all(), $job_id, $subtitle);
        }else{
            Cache::forever($job_id, 'failed');
        }

        return response()->json(['status' => true,'job_id' => $job_id]);

    }

    public function checkStatus(Request $request){
        $status = Cache::get($request->job_id);
        $subtitle = null;
        if($status == "generated"){

            $array = explode('_', $request->job_id);
            $video = Video::with('playerOptions')->where('id', $array[0])->first();

            if($video->playerOptions() !== null){
                $video->playerOptions()->update(['subtitle_control' => 'true']);
            }

            $subtitle = VideoSubtitle::with('translatedSubtitles')->where('video_id', $array[0])->first();

            if($subtitle->sub_status == "completed"){

                $minutes = (int) round(ceil($video->duration/60));
                if($video->caption_minutes !== null){
                    $minutes = $minutes + ($video->caption_minutes);
                }
                $video->update(['caption_minutes' => $minutes]);

            }

        }
        return response()->json(['status' => $status, 'subtitle' => $subtitle]);
    }

    public function ownerStatus(Request $r)
    {
        $video = Video::find($r->id);
        if (isset($video->owner)) {
            $user = User::find($video->owner);
            $status = $user->status_id;
            $billingStatus = $user->billing_status;
            $user->settings = $user->settings;
            $freePlay = $user->currentPlan->stripe_plan == 'free';
            if ($status != 1 || $billingStatus == 'cancelled') {
                return response()->json(['status' => false, 'user' => $user, 'free_user' => $freePlay]);
            }
            return response()->json(['status' => true, 'user' => $user, 'free_user' => $freePlay]);
        }

    }

    public function allOwnerVideos(Request $r)
    {
        $user = auth()->user();
        if ($user) {
            $videos = Video::where('owner', $user->id);
            if (!$r->allData) {
                return $videos->count();
            }
            $videos = $videos->get();
            foreach ($videos as $video) {
                $video->views_count = $video->view_count;
                $video->date_formatted = $video->created_date_formatted;
            }
            return $videos;
        }
        return [];
    }

    public function restrictVideos(Request $r)
    {
        foreach ($r->ids as $id) {
            $video = Video::find($id);
            if ($video) $video->update(['restricted' => 1]);
        }
    }

    public function singleVideoStats(Request $r)
    {
        $dateCondition = $this->dateCondition($r->days);
        $video = Video::find($r->id);
        $duration = $video->duration;
        $calculatedEngagement = [];
        $interval = round($duration / 16);
        $index = 0;
        for ($i = 0; $i < $duration; $i++) {

            if ($i % $interval === 0) {
                $views = Statistic::where('video_id', $video->id)->where($dateCondition)->where('event', 'video_view')->where("watch_start", '<=', $i)->where('watch_end', '>', $i)->count();
                $dropped = Statistic::where('video_id', $video->id)->where($dateCondition)->where('event', 'video_view')->where('watch_end', '=', $i)->count();
                $time = gmdate('i:s', $i);
                $calculatedEngagement[$index]['dropped'] = $dropped;
                $calculatedEngagement[$index]['viewed'] = $views;
                $calculatedEngagement[$index]['duration'] = $time;
                $index++;
            }
        }
        $calculatedEngagement['events'] = $this->videoEvents($video);
        return $calculatedEngagement;
    }

    protected function dateCondition($days)
    {
        if (is_numeric($days)) {
            $date = Carbon::now()->subDays($days);
            return [['created_at', '>', $date]];
        } else {
            switch ($days) {
                case 'last_year':
                    $startDate = Carbon::now()->subYear()->startOfYear();
                    $endDate = Carbon::now()->subYear()->endOfYear();
                    return [['created_at', '>=', $startDate], ['created_at', '<=', $endDate]];
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear();
                    return [['created_at', '>=', $startDate]];
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    return [['created_at', '>=', $startDate], ['created_at', '<=', $endDate]];
                    break;
                case 'this_month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    return [['created_at', '>=', $startDate], ['created_at', '<=', $endDate]];
                    break;
                default:
                    return [['created_at', '<', Carbon::now()]];
                    break;
            }
        }
    }


        public function snapUpload(Request $r)
    {
        $file = $r->file('video');
        $drm = $r->drm_protection === 'true';
        $project = Project::find($r->project_id);
        $type = $r->type;
        try {
            $video = new Video();
            $video->video_id = generate_video_unique_id();
            $video->video_type = 2;
            $video->title = $r->snapTitle;
            $video->project = $project->id;
            $video->owner = $project->owner;
            $video->team = $project->team;
            $video->filename = $r->snapTitle;
            $video->thumbnail = '';
            $video->path = '';
            $video->files = '';
            $video->duration = $r->duration;
            $video->duration_formatted = $r->duration_formatted;
            $video->transcoding_progress = 100;
            $video->transcoding_size_source = $file->getSize();
            $video->transcoding_size_out = '280449559';
            $video->transcoding_price = '0.0';
            $video->transcoding_badnwidth = 0;
            $video->published_on_stage = false;
            $video->transcoding_duration = $r->duration;
            $video->save();


            $video->videoDescription()->create([
                'description' => $r->snapDesc
            ]);

            if ($type && $type == 'shared') {
                $sharedSnap = SharedSnap::find($r->shared_snap_id);
                if ($sharedSnap) {
                    if ($sharedSnap->completed == 0 || !$sharedSnap->completed) {
                        $sharedSnap->update([
                            'video_id' => $video->id,
                            'creator_name' => $r->creator_name,
                            'creator_email' => $r->creator_email,
                            'completed' => 1
                        ]);
                    } else {
                        $sharedSnap = $this->addSharedSnap($video, $r, $sharedSnap->snap_label_id);
                    }
                }
            }
            
            $filename = md5(rand()).'.mp4';
            //$path = public_path().'/temp/video/';
            $path = storage_path('uploads/');
            $file->move($path, $filename);


            $videoPath = storage_path('uploads/').$filename;
            //$videoPath = public_path().'/temp/video/'.$filename;
            UploadVIdeoToWasabi::dispatch($video, $videoPath, $drm, 'snap');
            $snapPage = SnapPage::defaultData($video->id);
            addToLog(['user_id' => $project->owner,
                'activity_type' => 'video_upload',
                'subject' => "Uploaded a video: <span class='activity-text'>$video->filename</span>"
            ]);

            return response([
                'success' => true,
                'message' => 'file saved successfully',
                'video' => $video,
                'snap_page_id' => isset($snapPage->id) ? $snapPage->id : false,
                'snap_hash_id' => isset($snapPage->snap_page_link) ? $snapPage->snap_page_link : false
            ], 200);
        } catch (\Exception $exception) {

            throw new \Exception(json_encode([
                'status' => false,
                'message' => $exception->getMessage()
            ]));
        }

    }

    protected function addSharedSnap(Video $video, $r, $snap_label_id)
    {
        return SharedSnap::create([
            'video_id'      => $video->id,
            'project_id'    => $r->project_id,
            'snap_label_id' => $snap_label_id,
            'creator_name'  => $r->creator_name,
            'completed'     => 1,
            'creator_email' => $r->creator_email,
        ]);
    }

    protected function videoEvents($video)
    {
        $data = [];
        $activeOptions = $video->playerOptions()->where(
            function ($q) {
                $q->where('interaction_during_active', '=', true)
                    ->orWhere('interaction_during_email_capture', '=', true)
                    ->orWhere('interaction_before_email_capture', '=', true)
                    ->orWhere('interaction_after_email_capture', '=', true);
            }
        )->get();
        foreach ($activeOptions as $option) {
            if ($option->interaction_during_active) {
                if (!isset($data[$option->interaction_during_time]))
                    $count = $option->stats->where('event', 'click')->count();
                $count ? $data[$option->interaction_during_time] = "$count Link Clickthroughs" : null;
            }

            if (
                isset($data[$option->interaction_during_email_capture]) ||
                isset($data['before']) ||
                isset($data['after'])
            ) continue;
            if ($option->interaction_during_email_capture) {
                $count = $option->stats->where('event', 'email_capture')->count();
                $count ? $data[$option->interaction_during_email_capture_time] = "$count Emails captured" : null;

            } elseif ($option->interaction_before_email_capture) {
                $count = $option->stats->where('event', 'email_capture')->count();
                $count ? $data['before'] = "$count Emails captured" : null;

            } elseif ($option->interaction_after_email_capture) {
                $count = $option->stats->where('event', 'email_capture')->count();
                $count ? $data['after'] = "$count Emails captured" : null;
            }
        }
        return ($data);

    }

    public function removeVideoPixelRetargeting(Request $request)
    {


        if ($request->type == "event") {

            VideoEventcode::where('id', $request->id)->delete();

        }
        if ($request->type == "base") {

            VideoBasecode::where('id', $request->id)->delete();

        }

        return response()->json(true);
    }

    public function saveWatchSession(Request $r)
    {
        $id = $r->watch_session_id;
        if ($id) {
            $watchId = VideoWatchSession::where('watch_session_id', $id)->first();
            $bandWidth = $this->usedBandwidth($r->user_id);
            if ($watchId) {
                $count = $watchId->count;
                $sessionStored = $watchId->update([
                    'count' => $count + 1
                ]);
            } else {
                $sessionStored = VideoWatchSession::create([
                    'watch_session_id' => $id,
                    'user_id' => $r->user_id
                ]);
            }
            return response()->json([
                'session' => $sessionStored,
                'bandwidth_not_exceeded' => $bandWidth['not_exceeded'],
                'bandwidth' => $bandWidth['bandWidth']
            ]);
        }
    }

    public function realtimeUsers(Request $r)
    {
        $user = auth()->user();
        $time = Carbon::now()->subSeconds(300);
        return $user->watchSessions->where('updated_at', '>', $time)->count();
    }

    public function fingerprintDetail(Request $request)
    {

        $data = [];

        foreach($request->data as $d){
            $data[$d['key']] = gettype($d['value']) == "array" ? json_encode($d['value']) : $d['value'];

        }
        $data['fkey'] = $request->fkey;
        $data['video_id'] = $request->video_id;
        $data['forensic_session_id'] = $request->forensic_session_id;

            VideoFingerprint::create($data);

        return response()->json(true);
    }

    public function reomveVideoSubtitle(Request $request){
        VideoSubtitle::where('id', $request->id)->delete();
        TranslatedSubtitle::where('subtitle_id', $request->id)->delete();
        return response()->json(true);
    }

    protected function usedBandwidth($id)
    {
        try {
            $user = User::find($id);
            $notExceeded = true;
            $bandWidth = 0;
            if ($user) {
                $lifeTimePlan = $user->lifeTimePlan;
                if ($lifeTimePlan) {
                    $used = $user->bandwidthCycle->bandwidth_usage;
                    $planName = $lifeTimePlan->plan->name;
                    switch ($planName) {
                        case 'Adilo Lifetime Video Hosting (Personal)':
                            $bandWidth = 107374182400;
                            if ($used >= $bandWidth) {
                                $notExceeded = false;
                            }
                        break;
            
                        case 'Adilo Lifetime Video Hosting (Marketer)':
                            $bandWidth = 536870912000;
                            if ($used >= $bandWidth) {
                                $notExceeded = false;
                            }
                        break;
            
                        case 'Adilo Lifetime Video Hosting (Commercial)':
                            $bandWidth = 1099511627776;
                            if ($used >= $bandWidth) {
                                $notExceeded = false;
                            }
                        break;
            
                        case 'Adilo ELITE Membership Upgrade':
                            $bandWidth = 2199023255552;
                            if ($used >= $bandWidth) {
                                $notExceeded = false;
                            }
                        break;
                    }
                }
            }
            return [
                'not_exceeded' => $notExceeded,
                'bandWidth' => $bandWidth,
            ];;
        } catch (\Exception $e) {
            return [
                'not_exceeded' => false,
                'bandWidth' => 0
            ];
        }
    }
}
