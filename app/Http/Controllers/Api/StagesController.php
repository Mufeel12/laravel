<?php

namespace App\Http\Controllers\Api;

use App\Comments;
use App\Http\Controllers\Controller;
use App\Repositories\Stage\StageRepository;
use App\Stage;
use App\User;
use App\Playlist;
use App\Video;
use App\Project;
use App\PublicPlaylist;
use App\VideoPublishSchedular;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic;
use Spatie\ImageOptimizer\OptimizerChain;
use DB;
use DateTime;
use App\Image;

class StagesController extends Controller
{
	protected $stageRepo;

	public function __construct(StageRepository $stageRepository)
	{
		$this->stageRepo = $stageRepository;
	}

	/**
	 * Display a listing of the resource.
	 * @param $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(Request $request)
	{
		$user = $request->user();
		$playlists = Playlist::with(['videos'=>function($q){
			return $q->orderBy('id','desc');
		}])->where('owner',$user->id)->get();
		$feature_videos = [];
		$popular_videos = [];
		$recent_videos = [];

		$popular_video_query = $this->stageRepo->getPopularVideosList($user, true);

		if ($popular_video_query) {
			foreach ($popular_video_query as $key => $row) {
				$popular_videos[$key] = $this->getVideoObj($row, $user);
			}
		}

		$feature_video_query = $this->stageRepo->getFeaturedVideosList($user, true);

		if ($feature_video_query) {
			foreach ($feature_video_query as $key => $row) {
				$feature_videos[$key] = $this->getVideoObj($row, $user);
			}
		}

		$recent_video_query = $this->stageRepo->getRecentVideosList($user, true);

		if ($recent_video_query) {
			foreach ($recent_video_query as $key => $row) {
				$recent_videos[$key] = $this->getVideoObj($row, $user);
			}
		}
		$popular_videos = array_slice($popular_videos, 0, 10);
		$recent_videos = array_slice($recent_videos, 0, 10);

		
		return response()->json([
			'result' => 'success',
			'videos' => [
				$feature_videos,
				$popular_videos,
				$recent_videos
			],
			 
		]);
	}

	public function getVideosList(Request $request)
	{
		$re_videos = [];
		$filter = $request->input('filter');

		$user = $request->user();

		if ($filter == 'feature') {
			$re_video_query = $this->stageRepo->getFeaturedVideosList($user, false, true, 36);
		} elseif ($filter == 'popular') {
			$re_video_query = $this->stageRepo->getPopularVideosList($user, false, true, 36);
		} else {
			$re_video_query = $this->stageRepo->getRecentVideosList($user, false, true, 36);
		}

		if ($re_video_query) {
			foreach ($re_video_query as $key => $row) {
				$re_videos[$key] = $this->getVideoObj($row, $user);
			}
		}

		return response()->json([
			'result' => 'success',
			'videos' => $re_videos
		]);
	}

	private function getVideoObj($row, $user)
	{
		$video = Video::find($row->id);
		if (!$video->duration && ($video->duration_formatted === '' || is_null($video->duration_formatted))) {
			$result = shell_exec('ffmpeg -i ' . escapeshellcmd($video->path) . ' 2>&1');
			preg_match('/(?<=Duration: )(\d{2}:\d{2}:\d{2})\.\d{2}/', $result, $match);
			if (isset($match[1])) {
				$duration = $match[1];
				$video->duration_formatted = $match[1];

				$duration = explode(':', $duration);
				$duration = $duration[0] * 60 * 60 + $duration[1] * 60 + $duration[2];
				$duration = round($duration);
				$video->duration = $duration;

				$video->save();
			}
		}

		$obj = $row;
		$obj->full = $video->full();
		$obj->made_at = time_elapsed_string($video->created_at, false, $user->settings->timezone);
		$obj->published_on = date('M j, Y', strtotime($video->created_at));

		$owner = User::find($video->owner);
		$obj->owner_id = $owner->id;
		$obj->owner_name = $owner->name;
		$obj->owner_photo = $owner->photo_url;
		$obj->owner_logo = $owner->settings->logo;
		$obj->owner_stages = $owner->stages;
		$obj->comments = $video->comments()->whereNull('parent_id')->get()->map(function ($el) {
			$el = Comments::clearComments($el);

			$el->commented_at = date('M j, Y', strtotime($el->created_at));
			$el->showReplyInput = false;
			$el->showReply = false;

			$el->children->map(function ($cEl) {
				$cEl->commented_at = date('M j, Y', strtotime($cEl->created_at));

				return $cEl;
			});

			return $el;
		});

		return $obj;
	}

	/**
	 * Stage video play page
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getVideoById(Request $request)
	{ 
		$user = $request->user();

		$video_id = $request->input('video_id');
		$re_video = $this->stageRepo->getVideoData($video_id);
		if ($re_video) {
			$re_video = $this->getVideoObj($re_video, $user);
		}
		$schedular = VideoPublishSchedular::where('video_id',$re_video->id)->first();
		if ($schedular === null) {
             
            $schedular = [
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
		$re_video->full->schedular = $schedular;
		return response()->json([
			'result' => 'success',
			'video'  => $re_video
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Stage $stage
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update(Request $request, Stage $stage)
	{
		$user = $request->user();
        $initials = User::getInitials($user);

        $logo_path = '/data/branding/';

		if ($request->has('flag')) {
			$flag = $request->input('flag');
			if ($flag == 'cover') {
				$tempFile = Image::convertBase64($request->input('cover_image'));
				$tempSave = Image::saveTempImage($tempFile, $user->id);
				if (!$tempSave['success']) return response($tempSave);
				$fileDetails = Image::getFileKey($tempSave['file_path'], $user->email, 'stage-cover');
				$bucket_upload = Image::uploadImageToBucket($fileDetails['filekey'], $fileDetails['path'], $fileDetails['size']);
				if ($bucket_upload['success']) {
					$stage->cover_image = $bucket_upload['file_path'];
					$stage->save();
				}
				Image::clearTemps('stage-logo', $user->id);
				return response()->json([
					'result'        => $bucket_upload['success'] ? 'success' : 'failed',
					'cropped_image' => $bucket_upload['success'] ? $bucket_upload['file_path'] : null
				]);
			} else if ($flag == 'cover-remove')
			{
				$delete = Image::deleteImages($user, 'stage-cover');
				if ($delete['success']) {
					$stage->cover_image = null;
				}
				// return response()->json([
				// 	'result' 	=> $delete['success'],
				// 	'message' 	=> $delete['message']
				// ]);

			} else if ($flag == 'visit-update') {
				$stage->first_visit = 0;
			} else if ($flag == 'logo') {
				$img_data = $request->input('logo');
				$filename = $initials . '-' . $user->id . '-logo.jpg';

				$this->logoFileMove($logo_path, $img_data, $filename);

				$user->settings->logo = asset($logo_path . $filename);
				$user->settings->save();

				return response()->json([
					'result'        => 'success',
					'cropped_image' => asset($logo_path . $filename)
				]);
			}

			$stage->save();
		} else {
			$params = $request->all();
			$radio_keys = [
				'show_website', 'show_phone_number', 'show_email', 'show_facebook', 'show_instagram', 'show_twitter'
			];

			$string_keys = [
				'about_title', 'about_description', 'website', 'phone_number', 'email', 'facebook', 'instagram', 'twitter'
			];

			foreach ($params as $key => $val) {
				if (false !== array_search($key, $radio_keys)) {
					$params[$key] = ($val || $val == '1') ? 1 : 0;
				} else if (false !== array_search($key, $string_keys)) {
					$params[$key] = htmlspecialchars($val);
				} else {
					continue;
				}
			}

			$stage->fill($params);
			$stage->save();
		}

		return response()->json([
			'result' => 'success'
		]);
	}


	public function updateAboutInfo(Request $request)
    {
        $user = $request->user();
        $params = $request->all();

        if (isset($params['stage']) && !is_null($params['stage'])){
            $user->stages->about_title = $params['stage']['about_title'] ?? $user->stages->about_title;
            $user->stages->about_description = $params['stage']['about_description'] ?? $user->stages->about_description;
            $user->stages->save();
            return response()->json(['result' => 'success']);
        }

        return response()->json(['result' => 'fail']);
    }


	private function logoFileMove($logo_path, $img_data, $filename)
	{
		if (!file_exists(public_path($logo_path))) {
			mkdir(public_path($logo_path), 0777);
		}

		if (file_exists(public_path($logo_path . $filename))) {
			unlink(public_path($logo_path . $filename));
		}

		$data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $img_data));

		file_put_contents(public_path($logo_path . $filename), $data);

		Stage::imageOptimize($logo_path, $filename);
	}
	function getStagePlayList(Request $request){
		$user = $request->user();
		$playlists = Playlist::with(['videos'=>function($q){
			return $q->orderBy('id','desc');
		}])->where('owner',$user->id)->get();
		$choosenplaylist = PublicPlaylist::where('user_id',$user->id)->get()->pluck('playlist_id');
		if($playlists){
			foreach($playlists as $key=>$playlist){
				$playlists[$key]->ago = $this->time_elapsed_string($playlist->created_at,false);
				if($playlist->videos!=null){ 
					foreach($playlist->videos as $k=>$videos){
						$playlists[$key]['videos'][$k]=$this->getVideoObj($videos, $user);
					}
				}
			}
		}
		return response()->json([
			'result' => 'success',
			'playlist' => $playlists,
			'publicplaylist' => $choosenplaylist
			 
		]);
	}
	function getStagePlayListByid(Request $request){
		$user = $request->user();
		$playlist_id = $request->input('playlist_id');
		$playlist = Playlist::with(['videos'=>function($q){
			return $q->orderBy('id','desc');
		}])->where(['owner'=>$user->id,'pid'=>$playlist_id])->first();
		$choosenplaylist = PublicPlaylist::where('user_id',$user->id)->get()->pluck('playlist_id');
		if($playlist){ 
				$playlist->ago = $this->time_elapsed_string($playlist->created_at,false);
				if($playlist->videos!=null){ 
					foreach($playlist->videos as $k=>$videos){
						// $playlist['videos'][$k] = $this->stageRepo->getVideoData($videos->video_id);
						$playlist['videos'][$k]=$this->getVideoObj($videos, $user);
					}
				}
			 
		}
		return response()->json([
			'result' => 'success',
			'playlist' => $playlist,
			'publicplaylist' => $choosenplaylist,
			'user' => $user
			 
		]);
	}
	function time_elapsed_string($datetime, $full = false) {
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);
	
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;
	
		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}
	
		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	}

	function managePublicPlaylist(Request $request){
		$user = $request->user();
		$playlist = $request->playlist;
		$data = [];
		PublicPlaylist::where('user_id',$user->id) ->delete();
		if(count($playlist)>0){
			foreach($playlist as $val){
				$d = ['playlist_id'=>$val,'user_id'=>$user->id,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')];
				$data[] = $d;
			}
		}
		PublicPlaylist::insert($data);

	}
}
