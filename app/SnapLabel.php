<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SnapLabel extends Model
{
    protected $fillable = ['label', 'owner_id'];

    public function sharedSnaps()
    {
        return $this->hasMany('App\SharedSnap');
    }

    public static function filterSnapLabel($filter)
    {

        $user = auth()->user();

        $videos = SnapLabel::with('sharedSnaps.video')->where('owner_id', $user->id)->get();

        if (!empty($filter)) {
            
            foreach ($filter as $key => $value) {
                $value = (array) $value;
                switch ($key) {
                    case 'date':
                    {
                        $videos = SnapLabel::filterDate($value, $videos);
                        break;
                    }
                    case 'title':
                    {
                        $videos = SnapLabel::filterTitle($value, $videos);
                        break;
                    }
                }
            }
        }

        $snapLabels = $videos->toArray();

       	$filtered = [];
        foreach ($snapLabels as $key => $snapLabel) {
            $snapLabel['shared_video'] = array_filter(array_column($snapLabel['shared_snaps'], 'video'));
            $filtered[] = $snapLabel;
        }
        return $filtered;
    }

    public static function filterDate($data, $modelData)
    {
        switch ($data['action']) {
            case 'last_upload':
                {
                    return $modelData->sortByDesc('created_at');
                }
            case 'last_update':
                {
                    return $modelData->orderBy('updated_at', 'desc');
                }

            case 'before_upload':
                {
                    return $modelData->where('created_at', '<', $data['value']);
                }
            case 'before_update':
                {
                    return $modelData->where('updated_at', '<', $data['value']);
                }
            case 'after_upload':
                {
                    return $modelData->where('created_at', '>', $data['value']);
                }
            case 'after_update':
                {
                    return $modelData->where('updated_at', '>', $data['value']);
                }
            default :
                {
                    return $modelData;
                }
        }
    }

    public static function filterTitle($data, $modelData)
    {
    	//return 'test';
        switch ($data['action']) {
            case 'is':
                {
                    return $modelData->where('label', '=', $data['value']);
                }
            case 'contain':
                {
                    return $modelData->where('label', 'like', '%' . $data['value'] . '%');
                }
            case 'notContain':
                {
                    return $modelData->where('label', 'not like', '%' . $data['value'] . '%');
                }
            default :
                {
                    return $modelData;
                }
        }
    }
}
