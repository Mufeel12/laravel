<?php

namespace App\Http\Controllers;


use App\Experiment\ThumbnailClickCount;
use App\Experiment\ThumbnailExperiment;
use App\Experiment\VideoExperiment;
use App\Experiment\VideoExperimentWinner;
use App\ContentSecurityLog;
use App\Thumbnail;
use App\Video;
use Illuminate\Http\Request as Request;
use Illuminate\Support\Str;
use Soumen\Agent\Services\Browser;
use Illuminate\Support\Facades\Cache;

class EmbedController extends Controller
{
    /**
     * Shows thumbnail image with play button
     *
     * @param Request $request
     * @param $id
     */
    public function preview(Request $request, $id, $width = 200, $height = 150)
    {
        $video = Video::where('video_id', $id)->first();
        if (!count($video)) {
            $video = Video::find($id);

            abort_unless(count($video) >= 1, 404);
        }

        Thumbnail::renderPreviewThumbnail($video, $width, $height);
    }

    public function show($id, Request $request, $startTime = 0)
    {
        // return $request;
        // TODO: make it start at start time
        // TODO: check if the project is private, display video only to logged in people that can view it
        $videoId = $id;
        $experiment = $request->ex;
        if ($experiment) {
            $video = Video::videoExperimentShuffle($videoId);

            $autoplay = $request->autoplay ? 'true' : 'false';
            $playTime = $request->start;
            if ($video && isset($video->id)) {
                return redirect()->to("/watch/$video->video_id?autoplay=$autoplay&start=$playTime");
            }
        }
        ;
        $session_id = Str::random(16);
        $video = Video::where('video_id', $id)->first();

        if (!$video) {
            $video = Video::find($id);
            abort_unless($video, 404);
        }

        // $experiment = Video::ifActiveExperiment($video);

        // if ($experiment) {
        //     $video = $experiment;
        // }

        /* Advanced content security ===================== */

        if($video->visual_watermarking == 1){
            ContentSecurityLog::create([
                'type' => 'visual',
                'user_ip' => $request->ip(),
                'session_id' => $session_id,
                'video_id' => $video->video_id,
            ]);
            $visual_sessions_count = ContentSecurityLog::where('video_id', $video->video_id)->where('type', 'visual')->count();
            $video->update(['visual_sessions_count' => $visual_sessions_count]);
        }

        if($video->drm_protection == 1){
            if($video->drm_sessions_count == null){
                $drm_sessions_count = 1;
            }else{
                $drm_sessions_count = ($video->drm_sessions_count + 1);
            }
            $video->update(['drm_sessions_count' => $drm_sessions_count]);
        }

        if($video->forensic_watermarking == 1){

            if($video->forensic_sessions_count == null){
                $forensic_sessions_count = 1;
            }else{
                $forensic_sessions_count = ($video->forensic_sessions_count + 1);
            }

            $video->update(['forensic_sessions_count' => $forensic_sessions_count]);
            $owner = \App\User::find($video->owner);
            $owner_folder = generate_owner_folder_name($owner->email);

            //$owner_folder = 'preciousngwu2gmailcom';

            $protocol_id = 'dash';

            if(strtolower(Browser::get()->family) === 'safari' || $video->drm_protection == 0){
                $protocol_id = 'hls';
            }

            $forensic_files = callForensicAPI([
                'user_ip' => $request->ip(),
                'session_id' => $session_id,
                'adilo_account' => $owner_folder,
                'video_name' => $video->video_id,
                'Protocol ID' => $protocol_id,
                'Drm status' => $video->drm_protection == 1 ? 'yes' : 'no'
            ]);
//            dd($forensic_files);

            if(!empty($forensic_files->URL)){
                Cache::forever('forensic_file_' . $video->video_id. '_' .$request->ip() , [
                    'url' => $forensic_files->URL,
                    'protocol' => $protocol_id,
                    'forensic_session_id' => $forensic_files->forensic_session_id
                ]);
            }

        }

        $params = [
            'video' => $video,
            'title' => $video->title
        ];
	
	if ($request->input('part') == 'true') {
            echo view('video', $params);
            die();
	}

	$t = $video->duration;
//        dd($video->created_at);
        $data = ['@context' => 'http://schema.org/',
		 '@type' => 'VideoObject',
		 'name' => $video->title,
		 'contentUrl' => $video->embed_url,
		 'duration' => sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60),
		 'thumbnailUrl' => $video->thumbnail,
		 'uploadDate' => \Carbon\Carbon::parse($video->created_at)->format("Y-m-d"),
		 'description' => $video->description()
	];


	$script = "".($video->videoBasecode !== null ? $video->videoBasecode->code : '')."<script type='application/ld+json'>".json_encode($data)."</script>";

	$script .= '<meta name="title" content="'.$video->title.'">
<meta name="description" content="'.$video->description().'">
<!-- Open Graph / Facebook -->
<meta property="og:type" content="video">
<meta property="og:url" content="'. $video->embed_url .'">
<meta property="og:title" content="'. $video->title .'">
<meta property="og:description" content="'.$video->description().'">
<meta property="og:image" content="'. $video->thumbnail .'">
<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="'.$video->embed_url.'">
<meta property="twitter:title" content="'.$video->title.'">
<meta property="twitter:description" content="Watch '.($video->title).' via @adilovideo">
<meta property="twitter:image" content="'.$video->thumbnail.'">';

	$view = view('video', $params);
	$view = str_replace("<title>Bigcommand</title>", "<title>". $video->title ."</title>". preg_replace("/\n/", "", $script), $view);
	echo $view;
	die();
    }

    public function thumbnail(Request $r, $id)
    {
        $video = Video::where('video_id', $id)->first();
        if ($video) return response()->json($video);
        else return response()->json(['id' => false]);
    }

}