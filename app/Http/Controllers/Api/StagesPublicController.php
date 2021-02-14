<?php

namespace App\Http\Controllers\Api;

use App\Comments;
use App\Http\Controllers\Controller;
use App\Repositories\Stage\StageRepository;
use App\User;
use App\UserSettings;
use App\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Spark;
use Laravel\Spark\Repositories\TeamRepository;
use App\Playlist;
use App\PublicPlaylist; 
use DateTime;
class StagesPublicController extends Controller
{
    protected $stageRepo;

    public function __construct(StageRepository $stageRepository)
    {
        $this->stageRepo = $stageRepository;
    }

    public function index(Request $request, $stage_id)
    {
        $user = $request->user();

        if (is_null($user)){
            if (isset($stage_id) && !is_null($stage_id)){
                $user_id = UserSettings::where('stage_public_url', 'LIKE', '%'. $stage_id .'%')->pluck('user_id')->first();
                if (!is_null($user_id) && $user_id != '') {
                    $user_row = User::find($user_id);
                    $user = User::getUserDetails($user_row);
                }
            }
        }

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

        return response()->json([
            'result' => 'success',
            'videos' => [
                $feature_videos,
                $popular_videos,
                $recent_videos
            ]
        ]);
    }


    public function getVideosList(Request $request, $stage_id)
    {
        $re_videos = [];
        $filter = $request->input('filter');

        $user = $request->user();

        if (isset($stage_id) && !is_null($stage_id)){
            $user_id = UserSettings::where('stage_public_url', 'LIKE', '%'. $stage_id .'%')->pluck('user_id')->first();
            if (!is_null($user_id) && $user_id != '') {
                $user_row = User::find($user_id);
                $user = User::getUserDetails($user_row);
            }
        }

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
            'videos' => $re_videos,
            'user' => $user
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
    public function getVideoById(Request $request,$stage_id)
    {
        
            if (isset($stage_id) && !is_null($stage_id)){
                 $user_id = UserSettings::where('stage_public_url', 'LIKE', '%'. $stage_id .'%')->pluck('user_id')->first();
                if (!is_null($user_id) && $user_id != '') {
                    $user_row = User::find($user_id);
                    $user = User::getUserDetails($user_row);
                }
            }


        $video_id = $request->input('video_id');
        $re_video = $this->stageRepo->getPublicVideoData($video_id);
        if ($re_video) {
            $re_video = $this->getVideoObj($re_video, $user);
        }

        return response()->json([
            'result' => 'success',
            'video'  => $re_video
        ]);
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getUserByStageId(Request $request)
    {
        $user = Auth::user();

        if (isset(request()->stage_id) && !is_null(request()->stage_id)){
            $user_id = UserSettings::where('stage_public_url', 'LIKE', '%'. request()->stage_id .'%')->pluck('user_id')->first();
        } else {
            $user_id = UserSettings::where('stage_public_url', $request->header('referer'))->pluck('user_id')->first();
        }

        if (!is_null($user_id)) {
            $user = User::find($user_id);
        }

        return !is_null($user)
            ? ['result' => 'success', 'user' => User::getUserDetails($user)]
            : abort(404);
    }
    function storeComment(Request $request, $stageid,$id,$user_id){
        $video_file = Video::findOrfail($id);

        /**
         * In this case I fetch all data from request... need test for `parent_id`
         */
        $data = $request->all(); // Need do it better. If only request have 'is_readed'. need update just read
        //-------------------------------------------------------------------------
        $parent_id = $request->input('parent_id');
        $parent = $video_file->comments->find($parent_id); // Must return null if no parent found
        $user = User::find($user_id);

        /**
         * Commenting
         */
        # remove meta-values
        if (isset($data['returnHtml']))
            unset($data['returnHtml']);
        # 91919191919191919191919191 special value: means don't save video time at all
        if ($data['video_time'] == 91919191919191919191919191)
            $data['video_time'] = null;
        if (!isset($data['creator_id']) || empty($data['creator_id']))
            $data['creator_id'] = $user->id;

        $comment = $video_file->comment($data, $user, $parent);

        $comment = Comments::clearComments($comment);

        $teamId = $video_file->team;
        $team = Spark::interact(TeamRepository::class . '@find', [$teamId]);
        $usersInvolved = $team->users();
        $creator = $user;

        if (($usersInvolved)) {
            $usersInvolved->each(function ($user) use ($comment, $video_file, $creator) {
                // For all except current user
                if ($user->id != $comment->creator_id) {
                    $user->notify(new \App\Notifications\Comment($video_file, $creator, $user, $comment));
                }
            });

        }
	
		$comment->commented_at = date('M j, Y', strtotime($comment->created_at));
		$comment->showReplyInput = false;
		$comment->showReply = false;

        return response()->json($comment);
    }
    function getStagePlayList(Request $request, $stage_id){
		if (isset($stage_id) && !is_null($stage_id)){
            $user_id = UserSettings::where('stage_public_url', 'LIKE', '%'. $stage_id .'%')->pluck('user_id')->first();
            if (!is_null($user_id) && $user_id != '') {
                $user_row = User::find($user_id);
                $user = User::getUserDetails($user_row);
                    $playlists = Playlist::select('playlists.*')->join('public_playlist', 'public_playlist.playlist_id', '=', 'playlists.id')->with(['videos'=>function($q){
                        return $q->orderBy('id','desc');
                    }])->where(['owner'=>$user->id])->get();
                    //$choosenplaylist = PublicPlaylist::where('user_id',$user->id)->get()->pluck('playlist_id'); 
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
                }
            return response()->json([
                'result' => 'success',
                'playlist' => $playlists,
 
            ]);
        
        }

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
    
    function getStagePlayListByid(Request $request,$stage_id){
		if (isset($stage_id) && !is_null($stage_id)){
            $user_id = UserSettings::where('stage_public_url', 'LIKE', '%'. $stage_id .'%')->pluck('user_id')->first();
            if (!is_null($user_id) && $user_id != '') {
                $user_row = User::find($user_id);
                $user = User::getUserDetails($user_row);
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
    }
    return response()->json([
        'result' => 'error',
        'playlist' => [],
        'publicplaylist' => []
         
    ]);
	}
}