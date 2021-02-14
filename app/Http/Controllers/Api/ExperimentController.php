<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Experiment\ThumbnailExperiment;
use App\Experiment\VideoExperiment;
use App\Statistic;
use App\Video;
use Carbon\Carbon;
use App\Http\Controllers\Api\Traits\ExperimentTrait;
use Elasticsearch\Endpoints\Cluster\State;
use Exception;
use Illuminate\Support\Facades\URL;

class ExperimentController extends Controller
{
    use ExperimentTrait;
    public $allowed_extensions = ['jpeg', 'jpg', 'gif', 'bmp', 'png'];

    public function upload_thumbnail(Request $request)
    {
        $file = $request->file('file');
        $type = $request->type;
        $video_id = $request->video_id;
        $project_id = $request->project_id;
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $name = $file->getFilename();
        $name = "$type-$name.$extension";
        $file_type = in_array($extension, $this->allowed_extensions) ? 'Image' : 'Video';

        if (!in_array($extension, $this->allowed_extensions)) {
            return response()->json(['success' => 0, 'message' => "Extension '$extension' not allowed.", 'status' => 'error']);
        }
        !is_dir(public_path("/img/video_thumbnails")) ? mkdir(public_path("/img/video_thumbnails")) : null;
        !is_dir(public_path("/img/video_thumbnails/$project_id")) ? mkdir(public_path("/img/video_thumbnails/$project_id")) : null;
        !is_dir(public_path("/img/video_thumbnails/$project_id/$video_id")) ? mkdir(public_path("/img/video_thumbnails/$project_id/$video_id")) : null;

        $image_url = URL::to("/img/video_thumbnails/$project_id/$video_id/$name");

        if (is_dir(public_path("/img/video_thumbnails/$project_id/$video_id"))) {
            $path = public_path("/img/video_thumbnails/$project_id/$video_id");
            foreach (scandir($path) as $fileD) {
                if ($fileD == '.' || $fileD == '..') continue;
                $file_to_delete = explode('-', $fileD);
                if ($file_to_delete[0] && $file_to_delete[0] == $type) {
                    unlink($path. '/' . $fileD);
                }
            }
        }

        $uploaded = $file->move(public_path("/img/video_thumbnails/$project_id/$video_id"), $name);
        if ($uploaded) {
            return response()->json(['success' => 1, 'img' => $image_url, 'message' => 'Thumbnail uploaded', 'status' => 'success', 'size' => $size, 'type' => $file_type]);
        }
        return response()->json(['success' => 0, 'message' => 'Failed to uploaded', 'status' => 'error']);
    }

    public function save_experiment(Request $request)
    {
        $inserted = false;
        $data = $this->validate_data($request);

        $exists = ThumbnailExperiment::where([
            'project_id' => $data['project_id'],
            'active'     => 1,
            'video_id'   => $data['video_id']
        ]);

        $insert_experiment = $data;
        $overlay = $data['overlay'];

        unset($insert_experiment['overlay']);
        unset($insert_experiment['video']);
        unset($insert_experiment['thumbnails']);
        $videoData = [];
        if ($exists->count() > 0) {
            $exists->update($insert_experiment);
            $experiment = $exists->first();
            $inserted = $this->storeThumbnails($experiment, $overlay, $data);
            $videoData = $this->getVideoData($experiment->video_id, $experiment);
            return response()->json(['success' => 1, 'message' => 'Experiment updated!', 'status' => 'success', 'videoData' => $videoData]);
        } else {
            $experiment = ThumbnailExperiment::create($insert_experiment);
            $videoData = $this->getVideoData($experiment->video_id, $experiment);
        }

        $inserted = $this->storeThumbnails($experiment, $overlay, $data);
        if ($inserted) {
            addToLog(['user_id'=>$request->user()->id,
            'activity_type'=>'created_experiment',
            'subject'=>"Create a new experiment: <span class='activity-content'>$request->title</span>"
            ]);
            return response()->json(['success' => 1, 'message' => 'Experiment Saved', 'status' => 'success', 'videoData' => $videoData]);
        }
        return response()->json(['success' => 0, 'message' => 'Experiment Not Saved', 'status' => 'error']);
    }

    protected function storeThumbnails($experiment, $overlay, $data)
    {
        if ($experiment) {
            $inserted = $experiment->clickCounts()->create([
                'type'          => 'one',
                'clicks'        => 0,
                'url'           => $data['thumbnails']['one'],
                'overlay_text'  => $overlay['a'] ?: null
            ]);
            $inserted = $experiment->clickCounts()->create([
                'type'      => 'two',
                'clicks'    => 0,
                'url'       => $data['thumbnails']['two'],
                'overlay_text'  => $overlay['b'] ?: null
            ]);
        }
        return $inserted;
    }

    public function updateExperiment(Request $r)
    {

        $experimentData = $r->experiment;
        $experiment = ThumbnailExperiment::find($experimentData['id']);
        unset($experimentData['id']);
        if ($experimentData['duration_toggle'] == 'reduce') {
            $duration = $experiment->duration - $experimentData['duration'];
            $experimentData['duration'] = $duration;
        } elseif ($experimentData['duration_toggle'] == 'extend') {
            $duration = $experiment->duration + $experimentData['duration'];
            $experimentData['duration'] = $duration;
        } elseif ($experimentData['duration_toggle'] == 'restart') {
            $experimentData['duration'] = $experimentData['duration'];
            $experimentData['created_at'] = Carbon::now();
            Statistic::where('experiment_id', $experiment->id)->update(
                [
                    'experiment_id' => null,
                    'experiment_click_id' => null
                ]
            );
        }
        unset($experimentData['duration_toggle']);
        $update = $experiment->update($experimentData);
        if ($update) return response()->json(['success' => 1, 'message' => 'Experiment Updated!']);
        return response()->json(['success' => 1, 'message' => 'Experiment failed to update!']);
    }

    public function add_experiment_clicks(Request $request)
    {
        $exists = ThumbnailExperiment::where('active', 1)
            ->where('project_id', $request->project_id)
            ->where('video_id', $request->video_id)->first();
        if ($exists) {
            $exists->clickCounts()->where('url', $request->url)->increment('clicks');
        }
    }

    public function curvedImages($justAB=false)
    {
        $baseUrl    =  URL::to('/');
        $armA       = "/img/experiment_images/curve.png";
        $armAB      = "/img/experiment_images/curve-ab.png";
        if (!file_exists(public_path($armA)) || !file_exists(public_path($armAB))) {
            return response()->json(['success' => 0, 'message' => 'Curved Images Missing', 'status' => 'error']);
        }
        if ($justAB) {
            return "$baseUrl/$armAB";
        }
        return response()->json(['success' => 1, 'message' => 'Curved Images', 'status' => 'success', 'images' => [
            'armA'  => "$baseUrl/$armA",
            'armAB' => "$baseUrl/$armAB"
        ]]);
    }

    public function randomFrames(Request $r)
    {
        $thumnails = Video::find($r->video_id)->thumbnails;
        if ($thumnails) {
            return response()->json(['success' => 1, 'thumbnails' => json_decode($thumnails)]);
        }
        return response()->json(['success' => 0]);
    }

    public function allExperiments(Request $request)
    {
        $id = $request->id;
        $experiments = ThumbnailExperiment::where('project_id', $id)->get();
        $videoExperiments = VideoExperiment::where('project_id', $id)->get();
        $data = [];
        foreach ($experiments as $experiment) {

            $clicks = $experiment->clickCounts;
            if (!$clicks) continue;
            $thumbnailA = $clicks->first();
            $thumbnailB = $clicks->last();
            $date = Carbon::parse($experiment->created_at);
            $collect['title'] = $experiment->title;
            $collect['id'] = $experiment->id;
            $collect['type']  = $experiment->type;
            $collect['created_at'] = $this->formatDate($date);
            $collect['ends_at']    = $this->formatDate($date->addDays($experiment->duration));
            $collect['time_remaining'] = $this->remainingTime($experiment);
            $collect['thumbnails']['a'] = $thumbnailA ? $thumbnailA->url : null;
            $collect['thumbnails']['b'] = $thumbnailB ? $thumbnailB->url : null;
            $collect['file_details'] = $this->fileDetails($clicks);
            $collect['goals'] = 'play_rate';
            $collect['winning_thumbnail'] = $this->winning_thumbnail($experiment);
            $collect['video'] = $this->getVideoData($experiment->video_id, $experiment);
            $collect['duration'] = $experiment->duration;
            $collect['active'] = $experiment->active;
            $collect['action'] = $experiment->action;
            $collect['end_date'] = $this->formatDate(Carbon::parse($experiment->end_date));
            // $collect['curved_image'] = $this->curvedImages(true);
            $data[] = $collect;
        }

        foreach ($videoExperiments as $experiment) {
            $date = Carbon::parse($experiment->created_at);
            $collect['title'] = $experiment->title;
            $collect['id'] = $experiment->id;
            $collect['type']  = 'Videos';
            $collect['created_at'] = $this->formatDate($date);
            $collect['ends_at']    = $this->formatDate($date->addDays($experiment->duration));
            $collect['goals'] = json_decode($experiment->goals);
            $collect['time_remaining'] = $this->remainingTime($experiment);
            $collect['video_a'] = $this->getVideoData($experiment->video_id_a, $experiment);
            $collect['video_b'] = $this->getVideoData($experiment->video_id_b, $experiment);
            $collect['duration'] = $experiment->duration;
            $collect['active'] = $experiment->active;
            $collect['action'] = $experiment->action;
            $collect['stats'] = $this->videoStats($experiment);
            $collect['end_date'] = $this->formatDate(Carbon::parse($experiment->end_date));
            $data[] = $collect;
        }
        return $data;
    }

    protected function formatDate($dateTime)
    {
        $date = $dateTime->format('jS F Y');
        $time = $dateTime->format('h:i A');
        return "$date at $time";
    }

    protected function getVideoData($id, $experiment)
    {
        $video = Video::find($id);
        $description = $video->videoDescription;
        $views = Statistic::where(['video_id' => $video->id, 'event' => 'video_view']);
        if ($experiment->type == 'Thumbnails') {
            $views = $views->where('experiment_id', $experiment->id)->count();
        } else {
            $views = $views->where('video_experiment_id', $experiment->id)->count();
        }
        $date  = $video->date_formatted;
        $video->embed_url = $video->embed_url;
        $video->description = $description ? $description->description : null;
        $video->experiment_data = $experiment;
        $video['date']  = $date;
        $video['all_views'] = $views;

        return $video;
    }

    public function validate_data($request)
    {
        $data = $request->validate([
            'title' => 'required',
            'video' => 'required',
            'duration' => 'required',
            'action' => 'required',
            'thumbnails' => 'required',
            'project_id' => 'required',
            'type'  => 'required',
            'overlay' => 'required'
        ]);
        if ($data) {
            $data['video_id'] = $data['video']['id'];
            $data['active'] = 1;
            return $data;
        }
        return false;
    }

    public function experimentTitle(Request $request)
    {
        $type = $request->type;
        if ($type == 'Thumbnails') {
            $exists = ThumbnailExperiment::where([
                'project_id' => $request->project_id,
                'title' => $request->title,
                'type'  => $request->type
            ])->count();
        } else {
            $exists = VideoExperiment::where([
                'project_id' => $request->project_id,
                'title' => $request->title,
            ])->count();
        }
        if ($exists) {
            return response()->json(['success' => 0, 'message' => 'Title must be unique.']);
        }
       
        return response()->json(['success' => 1, 'message' => 'Title available']);
    }

    protected function fileDetails($files)
    {
        $data = ['a' => $files->first(), 'b' => $files->last()];
        $collect = [];
        foreach ($data as $index => $d) {
            if ($d) {
                $headers = get_headers($d->url, TRUE);
                $filesize = $headers['Content-Length'];
                $type = $headers['Content-Type'];
                $collect[$index]['size'] = $this->formatSizeUnits($filesize);
                $sizeExploded = explode('/', $type);
                $collect[$index]['type'] = ucfirst(count($sizeExploded) ? $sizeExploded[0] : 'NULL');
                $collect[$index]['views'] = $d->clicks;
            }
        }
        return $collect;
    }


    function filesize_formatted($size)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    public function getFileDetails(Request $r)
    {
        $ch = curl_init($r->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD_T);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        return response()->json(
            ['size' => $this->filesize_formatted($size), 'type' => $type]
        );
    }

    public function thumbnailDetails(Request $r)
    {
        try {
            $video_id = $r->video_id;
        $experiment = ThumbnailExperiment::where(['video_id' => $video_id, 'active' => 1])->first();
        $data = [];
        if (!$experiment) return response()->json(['success' => 0, 'message' => 'No experiment found!']);
        $clicks = $experiment->clickCounts;
        $data['a'] = $this->calculate_stats($clicks->first());
        $data['b'] = $this->calculate_stats($clicks->last());
        return response()->json(['success' => 1, 'data' => $data]);
        } catch ( Exception $e) {
            \Log::info($e->getMessage());
        }
    }

    public function experimentActions(Request $r)
    {
        $experiment = $r->experiment;
        $action = $r->action;

        $id = $experiment['id'];
        if ($experiment['type'] == 'Thumbnails') $experiment = ThumbnailExperiment::find($id);
        else $experiment = VideoExperiment::find($id);

        switch ($action) {
            case 'delete':
                return $this->deleteExperiment($experiment);
            break;
            case 'mark_complete':
                return $this->markComplete($experiment);
            break;
            case 'restart_experiment':
                return $this->restartExperiment($experiment);
            break;
            case 'reset_stats':
                return $this->resetStats($experiment);
            break;
        }
    }

    public function deleteExperiment($experiment)
    {
        $deleted = $experiment->delete();
        if ($deleted) return response()->json(['success' => 1, 'message' => "Experiment Deleted!"]);
        return response()->json(['success' => 0, 'message' => "No experiment found with id $experiment->id"]);
    }

    protected function markComplete($experiment)
    {
        $marked = $experiment->update(
            [
                'active' => 2,
                'end_date' => Carbon::now()
            ]
        );
        if ($marked) return response()->json(['success' => 1, 'message' => "Experiment Marked as Complete."]);
        return response()->json(['success' => 0, 'message' => "No experiment found with id $experiment->id"]);
    }

    protected function restartExperiment($experiment)
    {
        $experiment['video_ids'] = [$experiment->video_id_a, $experiment->video_id_b];
        $checkRestarted = $this->checkRestart($experiment, false);
        if ($checkRestarted['success'] == 1) {
            unset($experiment['video_ids']);
            $restarted = $experiment->update(['active' => 1]);
            if (!$experiment->type) {
                $experiment->winner()->delete();
            }
    
            if ($restarted) return response()->json(['success' => 1, 'message' => "Experiment Restarted."]);
            return response()->json(['success' => 0, 'message' => "No experiment found with id $experiment->id"]);
        } else {
            return $checkRestarted;
        }
    }

    protected function resetStats($experiment)
    {
        $reset = false;
        
        if ($experiment->type == 'Thumbnails') {
            $reset = Statistic::where('experiment_id', $experiment->id)->update(
                [
                    'experiment_id' => null,
                    'experiment_click_id' => null
                ]
            );
        } else {;
            $reset = Statistic::where('video_experiment_id', $experiment->id)->update(
                ['video_experiment_id' => null]
            );
        }
        if ($reset) return response()->json(['success' => 1, 'message' => "Experiment Reset."]);
        return response()->json(['success' => 0, 'message' => "No experiment found with id $experiment->id"]);
    }

    public function checkDuration(Request $r)
    {
        $type = $r->type;
        if ($type == 'thumbnails') $experiment = ThumbnailExperiment::find($r->experiment_id);
        else if ($type == 'videos') $experiment = VideoExperiment::find($r->experiment_id);

        $today = Carbon::today();
        $endDate = Carbon::parse($r->created_at)->addDays($experiment->duration);
        $newEndDate = Carbon::parse($today)->addDays($r->duration);
        $daysLeft = !Carbon::parse($today)->gt($endDate) ? Carbon::parse($today)->diffInDays($endDate) : 0;

        if ($r->duration > $daysLeft) {
            return response()->json(['success' => 0, 'message' => 'The value must be less than the remaining number of days.']);
        }
        return response()->json(['success' => 1, 'message' => 'The value can be used.']);
    }
    
    public function saveVideoExperiment(Request $r)
    {
        $data = $r->validate([
            'title'     => 'required',
            'videos_ids'=> 'required',
            'goals'     => 'required',
            'duration'  => 'required',
            'action'    => 'required',
            'project_id'=> 'required',
            'active'    => 'integer',
            'id'        => 'integer',
            'restart'   => 'boolean'
        ]);

        $data['video_id_a'] = $data['videos_ids'][0];
        $data['video_id_b'] = $data['videos_ids'][1];
        $data['goals'] = json_encode($data['goals']);
        if (isset($data['id'])) {
            if ($data['restart']) {
                Statistic::where('video_experiment_id', $data['id'])->update(['video_experiment_id' => null]);
                $data['created_at'] = Carbon::now();
            }
            unset($data['restart']);
            $experiment = VideoExperiment::find($data['id']);

            $experiment->update($data);
            $id = $data['id'];
        } else {
            unset($data['id']);
            unset($data['restart']);
            $exists = VideoExperiment::where('active', 1)
                ->where(function ($q) use ($data) {
                    $q->whereIn('video_id_a', $data['videos_ids'])
                        ->orWhereIn('video_id_b', $data['videos_ids']);
                });
            if ($exists->count()){
                $experiment = $exists->first();
                VideoExperiment::find($experiment->id)->update($data);
                $videoDetails = [
                    'a' => $this->getVideoData($experiment->video_id_a, $experiment),
                    'b' => $this->getVideoData($experiment->video_id_b, $experiment),
                ];
                return response()->json(['success' => 1, 'message' => 'Experiment Updated!', 'id' => $experiment->id, 'videoData' => $videoDetails]);
            }
            unset($data['videos_ids']);
            $experiment = VideoExperiment::create($data);
            $id = $experiment->id;
        }
        $videoDetails = [
            'a' => $this->getVideoData($experiment->video_id_a, $experiment),
            'b' => $this->getVideoData($experiment->video_id_b, $experiment),
        ];
        return response()->json(['success' => 1, 'message' => 'Experiment Saved!', 'id' => $id, 'videoData' => $videoDetails]);

    }

    protected function remainingTime($experiment)
    {
        $endDate        = Carbon::parse($experiment->created_at)->addDays($experiment->duration);
        $remainingDays  = 0;
        $remainingHours = 0;

        if ($endDate->gt(Carbon::now())) {
            $remainingDays  = Carbon::now()->diffInHours($endDate) > 23 ? Carbon::now()->diffInDays($endDate) : 0;
            $remainingHours = Carbon::now()->addDays($remainingDays)->diffInHours($endDate);
        }
        return [
            'status'    => $experiment->active,
            'days'      => $remainingDays,
            'hours'     => $remainingHours
        ];
    }

    public function checkRestart($r, $json=true)
    {
        if ($r['type'] == 'thumbnails' || $r['type'] == 'Thumbnails') {
            $exists = ThumbnailExperiment::where([
                'project_id' => $r['project_id'],
                'video_id'   => $r['video_id'],
                'active'     => 1
            ]);
            if ($exists->count()) {
                if ($json) {
                    return response()->json(['success' => 0, 'message' => 'Another experiment for this video is already active.']);
                }
                return ['success' => 0, 'message' => 'Another experiment for this video is already active.'];
            }
        } else {
            $videoIds = $r['video_ids'];
            $fail = 0;
            foreach ($videoIds as $id) {
                $exists = VideoExperiment::where([
                    'project_id' => $r['project_id'],
                    'active'     => 1
                ])->where(function ($q) use ($id) {
                    $q->where('video_id_a', $id);
                    $q->orWhere('video_id_b', $id);
                });
                if ($exists->count()) $fail = $fail + 1;
            }
            if ($fail == 1) {
                if (!$json) return ['success' => 0, 'message' => 'Another active experiment is using one of the selected video.'];
                return response(['success' => 0, 'message' => 'Another active experiment is using one of the selected video.']);
            }

            if ($fail == 2) {
                if (!$json) return ['success' => 0, 'message' => 'Another active experiment is using both of the selected videos.'];
                return response(['success' => 0, 'message' => 'Another active experiment is using both of the selected videos.']);
            }
        }
        if (!$json) return ['success' => 1];
        return response()->json(['success' => 1]);
    }

    public function canBeRestarted(Request $r)
    {
        return $this->checkRestart($r);
    }

    public function videoCheck(Request $r)
    {
        $id = $r->id;
        $type = $r->type;
        if ($type == 'thumbnails') {
            $exists = ThumbnailExperiment::where([
                'active' => 1,
                'video_id' => $id
            ]);
            if ($exists->count() > 0) return response()->json(['success' => 0, 'message' => 'Video already used in a active experiment.']);
        } else {
            $exists = VideoExperiment::where('active', 1)->where(function ($q) use ($id) {
                $q->where('video_id_a', $id);
                $q->orWhere('video_id_b', $id);
            });
            if ($exists->count() > 0) return response()->json(['success' => 0, 'message' => 'Video already used in a active experiment.']);
        }

        return response()->json(['success' => 1, 'message' => 'Video available.']);
    }

    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

}
