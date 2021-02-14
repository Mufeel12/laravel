<?php

namespace App\Http\Controllers\Api;

use App\Snap;
use App\Video;
use App\Project;
use App\Statistic;
use App\SnapLabel;
use App\SharedSnap;
use Carbon\Carbon;
use App\VideoDescription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SharedSnapsController extends Controller
{
    public function index()
    {
        $filtered = [];
    	$snapLabels = SnapLabel::where('owner_id', Auth::user()->id)->with('sharedSnaps.video')->orderBy('updated_at', 'DESC')->get()->toArray();
        foreach ($snapLabels as $key => $snapLabel) {
            $snapLabel['shared_video'] = array_filter(array_column($snapLabel['shared_snaps'], 'video'));

            $filtered[$key] = $snapLabel;
        }
        return $filtered;
    }

    public function labelSnaps($labelId)
    {
        $user = Auth::user();
        $snapLabel = SnapLabel::where('id' , $labelId)->with('sharedSnaps')->first();
        if($snapLabel){
            $videoId = $snapLabel->sharedSnaps->pluck('video_id');
            $videoId = array_unique($videoId->toArray());
        }else{
            $videoId = [];
        }

        try {
            $snaps = Video::with('playerOptions')
                ->where('owner', $user->id)
                ->whereIn('id', $videoId)
                ->get();

            $filtered = [];
            $project = false;
            foreach ($snaps as $snap) {
                $sharedSnap = SharedSnap::where('video_id', $snap->id)->first();
                $description = VideoDescription::where('video_id', $snap->id)->first();
                $snap->shared_snap = $sharedSnap;
                $snap->project = Project::find($sharedSnap->project_id);;
                $snap->description = $description ? $description->description : '';
                $snap->embed_url = $snap->embed_url;
                $snap->snap_views = Statistic::where(
                    [
                        'video_id' => $snap->id,
                        'event' => 'video_view',
                    ]
                )->count();
                $filtered[] = $snap;
            }
            return ['snaps' => $filtered, 'user' => auth()->user(), 'label' => $snapLabel];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'error';
    }

    public function filterSharedSnaps(Request $request)
    {
        $filterArray = json_decode(json_encode($request->filter), true);
        return SnapLabel::filterSnapLabel($filterArray);
    }

    public function filterSharedSnapVideo(Request $request)
    {
        $filterArray = json_decode(json_encode($request->filter), true);
        return SharedSnap::filterSharedSnapVideo($filterArray, $request->labelId);
    }
}
