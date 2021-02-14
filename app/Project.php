<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Spark\Repositories\TeamRepository;
use Laravel\Spark\Spark;

/**
 * Class Project
 *
 * a project is the main categorisation of content on this platform
 *
 * @package App\Models
 */
class Project extends Model
{
    /**
     * Table
     * @var string
     */
    protected $table = 'projects';

    /**
     * Guarded attributes
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'project_id',
        'title',
        'owner',
        'team',
        'private',
        'archived'
    ];

    protected $hidden = [
        'project_access',
    ];

    protected $userId;


    public function getCreatedAtAttribute($value)
    {
        return time_according_to_user_time_zone($value);
    }


    public function getUpdatedAtAttribute($value)
    {
        return time_according_to_user_time_zone($value);
    }


    /**
     * Returns all projects for user team
     *
     * @param $team
     * @param bool $includeArchived
     * @return mixed
     */
    public static function getAllForTeam($filter, $team, $includeArchived = false, $user_id)
    {
        $projects = Project::with('access')
            ->where('team', $team);

        $team_owner_id = \App\Team::where('id', $team)->first()->owner_id;

        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'date':
                        {
                            $projects = self::filterDate($value, $projects);
                            break;
                        }
                    case 'title':
                        {
                            $projects = self::filterTitle($value, $projects);
                            break;
                        }
                }
            }
        }

        $projects = $projects->get();

        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'views':
                        {
                            $projects = self::filterViews($value, $projects);
                            break;
                        }
                    case 'clicks':
                        {
                            $projects = self::filterClicks($value, $projects);
                            break;
                        }
                    case 'leads':
                        {
//                            $projects = self::filterLeads($value, $projects);
                            break;
                        }
                }
            }
        }
        return $projects->map(function ($index) use ($includeArchived, $team_owner_id, $filter) {
            if (!$index->archived || ($index->archived && $includeArchived)) {
                $newIndex = $index;
                $newIndex->most_recent_videos = $index->getMostRecentVideos(2);
                $newIndex->comments_count = $index->getCommentsCount();
                $newIndex->video_count = $index->video_count;
                $newIndex->video_views_count = $index->video_views_count;
                $newIndex->video_clicks_count = $index->video_clicks_count;
                $newIndex->team_owner_id = $team_owner_id;

                $newIndex->videos = $index->videos();
                return $newIndex;
            }
        })->filter(function ($index) use ($user_id) {
            if ($index)
                return $index;
        })->values();
    }

    public function getVideoViewsCountAttribute()
    {
        return Statistic::where([
            'domain' => config('app.site_domain'),
            'project_id' => $this->id,
            'event' => 'video_view'
        ])->where(function ($q) {
            $q->where('statistics.watch_start', '<>', '0')
                ->orWhere('statistics.watch_end', '<>', '0');
        })
        ->where('statistics.watch_end', '<>', '0') 
            ->groupBy('watch_session_id')
            ->get()->count();
    }

    public function getVideoClicksCountAttribute()
    {
        return Statistic::where([
            'domain' => config('app.site_domain'),
            'project_id' => $this->id,
            'event' => 'clicks'
        ])
            ->groupBy('watch_session_id')
            ->count();
    }

    public function getVideoLeadsCountAttribute()
    {

    }

    public function getVideoCountAttribute()
    {
        return DB::table('videos')->where('project', $this->id)->count();
    }

    public function getCommentsCount()
    {
        return DB::table('comments')->where('title', $this->id)->count();
    }

    public function getMostRecentVideos($num)
    {
        return Video::where('project', $this->id)
            ->orderBy('id', 'DESC')
            ->limit($num)
            ->get();
    }

    /**
     * Returns true if user is owner of project
     *
     * @return bool
     */
    public function isAdmin()
    {
        $userId = Auth::id();

        return ($this->owner == $userId);
    }

    /**
     * Returns the team
     *
     * @return mixed
     */
    public function team()
    {
        return Spark::interact(TeamRepository::class . '@find', [$this->team]);
    }

    /**
     * Returns users
     *
     * @return mixed
     */
    public function users()
    {
        return $this->team()->users;
    }

    /**
     * Returns videos of project
     */
    public function videos($filter = [], $snap = false)
    {
        // Get videos
        $videos =  Video::where('project', $this->id);
        if ($snap) {
            $videos = $videos->where(function ($q) {
                $q->where('video_type', 2);
            });
        }
        $videos = $videos->orderBy('id', 'DESC');
            

        $videos = $videos->get();

        foreach ($videos as $i => $video) {
            $videos[$i]['views_count'] = Statistic::where([
                'domain' => config('app.site_domain'),
                'project_id' => $this->id,
                'video_id' => $video->id,
                'event' => 'video_view'
            ])->count();

            $videos[$i]['clicks_count'] = Statistic::where([
                'domain' => config('app.site_domain'),
                'project_id' => $this->id,
                'video_id' => $video->id,
                'event' => 'click'
            ])->count();
        }

        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                if (!$value) continue;
                switch ($key) {
                    case 'date':
                    {
                        $videos = self::filterDate($value, $videos);
                        break;
                    }
                    case 'title':
                    {
                        $videos = self::filterTitle($value, $videos);
                        break;
                    }
                    case 'views':
                        {
                            $videos = self::filterViews($value, $videos, 'videos');
                            break;
                        }
                    case 'clicks':
                        {
                            $videos = self::filterClicks($value, $videos, 'videos');
                            break;
                        }
                    case 'leads':
                        {
//                            $videos = self::filterLeads($value, $videos, 'videos');
                            break;
                        }
                }
            }
        }



        return $videos->map(function ($index) {
                $description = VideoDescription::where(['video_id' => $index->id])->first();

                $newIndex = $index;
                // Init get dynamic variables
                $newIndex->has_been_touched = $index->has_been_touched;
                $newIndex->date_formatted = $index->date_formatted;
                $newIndex->clicks = $index->clicks;
                $newIndex->views = $index->views;
                $newIndex->leads = $index->leads;
                $newIndex->is_imported = $index->is_imported;
                $newIndex->imported = $index->imported;
                $newIndex->scrumb = $index->scrumb;
                $newIndex->duration_formatted = $index->duration_formatted;
                $newIndex->description = !is_null($description) ? $description->description : '';
                return $newIndex;
            })->values();
//        return $videos;
    }

    public function access()
    {
        return $this->belongsToMany('App\User', 'project_access');
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
        switch ($data['action']) {
            case 'is':
                {
                    return $modelData->where('title', '=', $data['value']);
                }
            case 'contain':
                {
                    Log::info('%' . $data['value'] . '%');
                    return $modelData->where('title', 'like', '%' . $data['value'] . '%');
                }
            case 'notContain':
                {
                    return $modelData->where('title', 'not like', '%' . $data['value'] . '%');
                }
            default :
                {
                    return $modelData;
                }
        }
    }

    public static function filterViews($data, $modelData, $modelName = 'projects')
    {
        $field = $modelName === 'videos' ? 'views_count' : 'video_views_count';

        switch ($data['action']) {
            case 'equal':
                {
                    return $modelData->where($field, '=', $data['value']);
                }
            case 'between':
                {
                    return $modelData
                        ->where($field, '>=', $data['value']['from'])
                        ->where($field, '<=', $data['value']['to']);

                }
            case 'greater':
                {
                    return $modelData->where($field, '>', $data['value']);
                }
            case 'less':
                {
                    return $modelData->where($field, '<', $data['value']);
                }
            default :
                {
                    return $modelData;
                }
        }
    }

    public static function filterClicks($data, $modelData, $modelName = 'projects')
    {
        $field = $modelName === 'videos' ? 'clicks_count' : 'video_clicks_count';

        switch ($data['action']) {
            case 'equal':
                {
                    return $modelData->where($field, '=', $data['value']);
                }
            case 'between':
                {
                    return $modelData
                        ->where($field, '>=', $data['value']['from'])
                        ->where($field, '<=', $data['value']['to']);
                }
            case 'greater':
                {
                    return $modelData->where($field, '>', $data['value']);
                }
            case 'less':
                {
                    return $modelData->where($field, '<', $data['value']);
                }
            default :
                {
                    return $modelData;
                }
        }
    }

    public static function filterLeads($data, $modelData, $modelName = 'projects')
    {
        $field = $modelName === 'videos' ? 'leads_count' : 'video_leads_count';
        switch ($data['action']) {
            case 'equal':
                {
                    return $modelData->where($field, '=', $data['value']);
                }
            case 'between':
                {
                    return $modelData
                        ->where($field, '>=', $data['value']['from'])
                        ->where($field, '<=', $data['value']['to']);
                }
            case 'greater':
                {
                    return $modelData->where($field, '>', $data['value']);
                }
            case 'less':
                {
                    return $modelData->where($field, '<', $data['value']);
                }
            default :
                {
                    return $modelData;
                }
        }
    }
}
