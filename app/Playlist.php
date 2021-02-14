<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $table = 'playlists';

    protected $fillable = [
        'pid',
        'project',
        'owner',
        'title',
        'description'
    ];
    protected $appends = ['url'];
    public function syncVideos($videos) {
        $pl = $this;
        // delete all old videos
        \App\PlaylistVideo::where('playlist', $this->id)->delete();
        // add again
        collect($videos)->each(function($video) use ($pl) {
            // extract ids
            if (isset($video->id)) $video = $video->id;
            if (isset($video['id'])) $video = $video['id'];

            $v = new \App\PlaylistVideo([
                'playlist' => $pl->id,
                'video' => $video]);
            $v->save();
        });
    }

    public function videos()
    {
	return $this->hasManyThrough(\App\Video::class, \App\PlaylistVideo::class, 'playlist', 'id', 'id', 'video');    
    }

    public function getUrlAttribute() {
	$firstVideo = $this->videos()->first();
	return config('services.__adilo.ROOT_URL') . "/watch/" . @$firstVideo->video_id . "?list=" . $this->pid;
    }
}
