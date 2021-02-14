<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SharedSnap extends Model
{
    protected $fillable = ['video_id', 'project_id', 'snap_label_id', 'creator_name', 'completed', 'creator_email', 'shared_snap_id'];

    public function snapLabel()
    {
        return $this->belongsTo('App\SnapLabel');
    }

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function video()
    {
        return $this->belongsTo('App\Video', 'video_id');
    }

    public static function filterSharedSnapVideo($filter, $labelId)
    {

        $user = auth()->user();

        $snapLabel = SnapLabel::where('id' , $labelId)->with('sharedSnaps')->first();
        if($snapLabel){
            $videoId = $snapLabel->sharedSnaps->pluck('video_id');
            $videoId = array_unique($videoId->toArray());
        }else{
            $videoId = [];
        }

        $videos = Video::with('playerOptions')
                ->where('owner', $user->id)
                ->whereIn('id', $videoId)
                ->get();

        if (!empty($filter)) {
            
            foreach ($filter as $key => $value) {
                $value = (array) $value;
                switch ($key) {
                    case 'date':
                    {
                        $videos = Project::filterDate($value, $videos);
                        break;
                    }
                    case 'title':
                    {
                        $videos = Project::filterTitle($value, $videos);
                        break;
                    }
                    case 'views':
                    {
                        $videos = Project::filterViews($value, $videos, 'videos');
                        break;
                    }
                }
            }
        }

        $filtered = [];
        $project = false;
        foreach ($videos as $snap) {
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

        return $filtered;
        
    }

    
}
