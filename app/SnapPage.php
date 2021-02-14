<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Aws\CommandPool;
use Aws\S3\S3Client;


class SnapPage extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'owner_id',
        'snap_page_link',
        'video_id',
        'page_name',
        'title',
        'description',
        'logo',
        'call_menu',
        'button_text',
        'button_link',
        'button_color',
        'name',
        'desination',
        'mobile_no',
        'profile_pic',
        'facebook_link',
        'twitter_link',
        'instagram_link',
        'linkedin_link',
        'status',
    ];


    public function video()
    {
        return $this->belongsTo('App\Video');
    }

    public function relatedSnapVideos()
    {
        return $this->hasMany('App\RelatedSnapVideo');
    }

    public function snapPageMenus()
    {
        return $this->hasOne('App\SnapPageMenu');
    }

    /*public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }*/

    public function getUpdatedAtFormattedAttribute()
    {
        return Carbon::parse($this->updated_at)->diffForHumans();
    }

    public static function filterSnapPageData($filter)
    {

        $user = auth()->user();
        $videos = SnapPage::with('video')->where('owner_id', $user->id)
                    //->orderBy('id', 'DESC')
                    ->get();

        foreach ($videos as $i => $video) {
            $videos[$i]['views_count'] = Statistic::where([
                'domain' => config('app.site_domain'),
                'video_id' => $video->video->video_id,
                'event' => 'video_view'
            ])->count();

            $videos[$i]['clicks_count'] = Statistic::where([
                'domain' => config('app.site_domain'),
                'video_id' => $video->video->video_id,
                'event' => 'click'
            ])->count();
        }
        //return $videos;

        if (!empty($filter)) {
            
            foreach ($filter as $key => $value) {
                $value = (array) $value;
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
                }
            }
        }

        //return $videos;
        return $videos->map(function ($index) {

                $newIndex = $index;
                // Init get dynamic variables
                $newIndex->has_been_touched = $index->has_been_touched;
                $newIndex->updated_at_formatted = $index->date_formatted;
                $newIndex->clicks = $index->clicks;
                $newIndex->views = $index->views;
                $newIndex->leads = $index->leads;
                $newIndex->is_imported = $index->is_imported;
                $newIndex->imported = $index->imported;
                $newIndex->scrumb = $index->scrumb;
                $newIndex->duration_formatted = $index->duration_formatted;

                $project = Project::find($index->video->project);
                $newIndex->project = $project;

                return $newIndex;
            })->values();
    }

    public static function filterDate($data, $modelData)
    {
        $date = Carbon::parse($data['value'])->format('Y-m-d');
        switch ($data['action']) {
            case 'last_upload':
                {
                    //return $modelData->where('created_at', '>=',  $date);
                    return $modelData->sortByDesc('created_at');
                }
            case 'last_update':
                {
                    //return $modelData->where('updated_at', '>=', $date);
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

    public static function copySnapPage($data)
    {
        if (isset($data['snap_id']) && !is_null($data['snap_id'])) {

            $snapPage = SnapPage::find($data['snap_id']);

            /* check if model exists */
            if (!is_null($snapPage)) {
                $bucket = config('aws.bucket');
                $endpoint = config('aws.endpoint');

                /* get s3 client */
                $s3 = S3Client::factory(array(
                    'endpoint' => $endpoint,
                    'region' => config('aws.region'),
                    'version' => config('aws.version'),
                    'credentials' => config('aws.credentials')
                ));

                /* find owner and generate folder name */
                $owner = User::find($snapPage->owner_id);
                $ownerFolder = generate_owner_folder_name($owner->email);
                 
                $fileKey = "{$ownerFolder}/{$snapPage->snap_page_link}/";

                /* generate new video id and key */
                $newSnapPageLinkId = generateSnapPageLinkId();
                $newFileKey = "{$ownerFolder}/{$newSnapPageLinkId}";

                try {
                    /* try to get file objects */
                    $objects = $s3->getIterator('ListObjects', array(
                        'Bucket' => $bucket,
                        'Prefix' => "$fileKey"
                    ));
                } catch (\Exception $exception) {
                    return ['success' => false, 'message' => $exception->getMessage()];
                }

                $files = [];

                /* copy all files recursively */
                foreach ($objects as $object) {
                    $newFileName = str_replace("$fileKey", '', $object['Key']);

                    $files[] = $s3->getCommand('CopyObject', [
                        'Bucket' => $bucket,
                        'Key' => "{$newFileKey}/{$newFileName}",
                        'CopySource' => "{$bucket}/{$object['Key']}",
                    ]);
                }

                if (is_array($files) && !empty($files)) {
                    try {
                        /* put copied files to new generated folder */
                        CommandPool::batch($s3, $files);

                        /* create new video record in database with relations */
                        $newSnapPage = $snapPage->replicate();
                        $newSnapPage->snap_page_link = $newSnapPageLinkId;
                        $event = 'duplicated';
                        if (isset($data['snap_title']) && !is_null($data['snap_title'])) {
                            $newSnapPage->title = $data['snap_title'];
                        }
                        /*if (isset($data['copied_project_id']) && !is_null($data['copied_project_id'])) {
                            $newSnapPage->project = $data['copied_project_id'];
                            $event = 'copied';
                        }*/
                        $newSnapPage->save();

                        return ['success' => true, 'newSnapPage' => $newSnapPage, 'message' => "Your snap page has been {$event}."];
                    } catch (\Exception $e) {
                        return ['success' => false, 'message' => $e->getMessage()];
                    }
                } else {
                    return ['success' => false, 'message' => 'No files found in bucket'];
                }
            }

            return ['success' => false, 'message' => 'There is no snap page with the requested id'];
        }

        return ['success' => false, 'message' => 'You must send snap page id'];
    }

    public static function uploadImageToBucket($fileKey, $filePath, $fileSize)
    {
        $endpoint = config('aws.endpoint');
        $bucket = config('aws.bucket');
        \Log::info('1' . $fileKey);
        \Log::info('2' . $filePath);
        \Log::info('3' . $fileSize);
        $s3 = S3Client::factory(array(
            'endpoint' => $endpoint,
            'region' => config('aws.region'),
            'version' => config('aws.version'),
            'credentials' => config('aws.credentials')
        ));

        try {
            $s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $fileKey,
                'Body' => file_get_contents($filePath),
                'ContentLength' =>$fileSize,
                'ACL' => 'public-read'
            ));

            $filePath = $s3->getObjectUrl($bucket, $fileKey);
            return ['success' => true, 'filePath' => $filePath];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    public static function deleteAllBucketFiles($snapPageLink, $ownerId, $type)
    {
        $bucket = config('aws.bucket');
        $endpoint = config('aws.endpoint');

        /* get s3 client */
        $s3 = S3Client::factory(array(
            'endpoint' => $endpoint,
            'region' => config('aws.region'),
            'version' => config('aws.version'),
            'credentials' => config('aws.credentials')
        ));

        /* find owner and generate folder name */
        $owner = User::find($ownerId);
        $ownerFolder = generate_owner_folder_name($owner->email);

        if ($type == 'delete') {
            $fileKey = "{$ownerFolder}/{$snapPageLink}/"; 
        } else {
            $fileKey = "{$ownerFolder}/{$snapPageLink}/snap-page-{$type}/";     
        }

        /* try to delete objects */
        try {
            $s3->deleteMatchingObjects($bucket, $fileKey);
            return ['success' => true, 'message' => 'Your snap page image deleted successfully.'];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    public static function deleteMultipleSnapPage($snapPages){
        
        foreach ($snapPages as $snapPage) {
            
            try {
                $delete = SnapPage::deleteAllBucketFiles($snapPage->snap_page_link, $snapPage->owner_id, 'delete');

                if (isset($delete['success']) && $delete['success'] == true) {
                    $snapPage->delete();
                }

            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        return 'success';
    }

    public static function defaultData($videoId)
    {
        $video = Video::find($videoId);
        if ($video) {
            $user = User::find($video->owner);
            return self::create([
                'video_id' => $video->id,
                'owner_id' => $user->id,
                'snap_page_link' => str_random(8)
            ]);
        }
        return ['success' => false];
    }
}
