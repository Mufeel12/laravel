<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function index(Request $request) {
        return \App\Playlist::where('project', $request->input('project'))
			   ->with('videos')
			   ->get();
    }
    
    public function show(Request $request) {
        $playlist = \App\Playlist::where('id', $request->input('id'))
				 ->orWhere('pid', $request->input('id'))
				 ->with('videos')
				 ->firstOrFail();

        $playlist->videos = collect($playlist->videos)->map(function($video) {
            return $video->full();
        });
	$playlist->url = $playlist->url;

        return $playlist;
    }

    public function store(Request $request) {
        $attr = $request->all();
        $attr['owner'] = $request->user()->id;
        $attr['team'] = $request->user()->currentTeam()->id;
        $attr['pid'] = str_random(16);

        $project = \App\Project::find($attr['project']);

        abort_unless($project->team == $request->user()->currentTeam()->id, 401);

        $pl = new \App\Playlist($attr);
        $pl->save();
        $pl->syncVideos($request->input('videos'));

        return $pl;
    }

    public function save(Request $request) {
        $playlist = collect($request->input('playlist'))->except('owner')->toArray();
        
        $pl = \App\Playlist::where('id', $playlist['id'])
            ->orWhere('pid', $playlist['pid'])
            ->first();

        //abort_unless($pl->team == $request->user()->currentTeam()->id, 401);
        
        $pl->update($playlist);
        $pl->syncVideos($playlist['videos']);

        return $pl;
    }

    public function delete(Request $request) {
        $playlist = $request->input('playlist');
        $pl = \App\Playlist::where('id', $playlist['id'])
            ->orWhere('pid', $playlist['pid'])
            ->first();

        //abort_unless($pl->team == $request->user()->currentTeam()->id, 401);
        
        \App\PlaylistVideo::where('playlist', $pl->id)->delete();
        $pl->delete();

        return "success";
    }

}
