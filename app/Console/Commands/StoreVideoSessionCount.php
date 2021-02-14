<?php

namespace App\Console\Commands;

use App\ContentSecurityLog;
use App\VideoProcessingEvent;
use App\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StoreVideoSessionCount extends Command
{

    protected $signature = 'StoreVideoSessionCount';

    protected $description = 'StoreVideoSessionCount';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        //store drm session count

        $drm_data = doApiRequest([
            'action' => 'get_drm_cid_license_list',
            'startDate' => '2020-03-01',
            'endDate' => now()->toDateString()
        ]);

        $drm_cnt = [];

        if (isset($drm_data['success']) && $drm_data['success']) {
            foreach ($drm_data['result'] as $drm) {
                if(array_key_exists($drm['cid'], $drm_cnt)){
                    $drm_cnt[$drm['cid']] = $drm['licenseCnt'] + $drm_cnt[$drm['cid']];
                }else{
                    $drm_cnt[$drm['cid']] = $drm['licenseCnt'];
                }
            }
        }

        foreach($drm_cnt as $index => $cnt){
            Video::where('video_id', $index)->update(['drm_sessions_count' => $cnt]);
        }


        //store visual session count

        $contentdata = ContentSecurityLog::selectRaw('count(*) as total, video_id')->where('type', 'visual')->groupBy('video_id')->get();

        foreach($contentdata as $data){

            Video::where('video_id', $data->video_id)->update(['visual_sessions_count' => $data->total]);

        }



    }
}
