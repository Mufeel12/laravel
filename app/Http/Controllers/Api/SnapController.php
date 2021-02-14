<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Project;
use App\SharedSnap;
use App\SnapLabel;
use App\Statistic;
use App\Video;
use App\VideoDescription;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SnapController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        try {
            $snaps = Video::with('playerOptions')
                ->where([
                    'owner' => $user->id,
                    'video_type' => 2,
                ])
                ->with('sharedSnap')
                ->whereDate('created_at', '>', Carbon::now()->subDays(60))
                ->orderBy('updated_at', 'DESC')
                ->get();
            $filtered = [];
            $project = false;
            foreach ($snaps as $snap) {
                $project = Project::find($snap->project);
                $description = VideoDescription::where('video_id', $snap->id)->first();
                $snap->updated_at_formatted = $snap->date_formatted;
                $snap->project = $project;
                $snap->description = $description && isset($description->description) && $description->description != 'undefined' ? $description->description : '';
                $snap->embed_url = $snap->embed_url;
                $snap->snap_views = Statistic::where(
                    [
                        'video_id' => $snap->id,
                        'event' => 'video_view',
                    ]
                )->count();
                $filtered[] = $snap;
            }
            return ['snaps' => $filtered, 'user' => auth()->user()];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'error';
    }

    public function storeSharedSnaps(Request $r)
    {
        $data = $r->validate([
            'project_id' => 'required|integer',
            'label' => 'required|string'
        ]);
 
        $userId = auth()->user()->id;

        $label['owner_id'] = $userId;
        $label['label'] = $data['label'];
        
        $shared['project_id'] = $data['project_id'];
        $shared['owner_id'] = $userId;
        $shared['completed'] = 0;
        $shared['shared_snap_id'] = str_random(8);

        $myLabel = SnapLabel::where($label)->first();
        if (!$myLabel) {
            $myLabel = SnapLabel::create($label);
        }
        $insertShared = $myLabel->sharedSnaps()->create($shared);

        if ($insertShared->id){
            // $encryptedID = bin2hex('snap_label='.$insertShared->id);
            return ['id' => $insertShared->shared_snap_id];
        } 
    }

    public function updateSharedSnaps(Request $r)
    {

    }

    public function sharedSnaps(Request $r)
    {
        $label = SharedSnap::find($r->id)->snap_label;
        return view('snaps.index', compact('label'));
    }

    public function snapLabels(Request $r)
    {
        return SnapLabel::where('owner_id', auth()->user()->id)->pluck('label');
    }

    public function snapDetails(Request $r)
    {
        $project = null;
        $id = $r->id;
        $shared = SharedSnap::where('shared_snap_id', $id)->first();

        if ($shared) {
            $project = DB::table('projects')->where('id', $shared->project_id)->first();
            return [
                'project' => $project,
                'shared'  => $shared,
                'user'    => auth()->user()
            ];
        }
    }

    public function show($id)
    {   
        $snap = Video::with('videoDescription')->find($id);
        if ($snap && $snap->video_type == 2) return $snap->full();
        return abort(404, 'Snap not found');
    }

}
