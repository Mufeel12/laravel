<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Project;
use App\RelatedSnapVideo;
use App\Statistic;
use App\Snap;
use App\SnapPage;
use App\SnapPageMenu;
use App\Video;
use App\VideoDescription;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\User;

class SnapPageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        try {

            $snapPages = SnapPage::with('video')
                    ->where([
                        'owner_id' => $user->id,
                    ])
                    ->orderBy('updated_at', 'DESC')
                    ->get(); 

            $filtered = [];
            $project = false;
            foreach ($snapPages as $snapPage) {
                $project = Project::find($snapPage->video->project);

                $snapPage->updated_at_formatted = $snapPage->updated_at_formatted;
                $snapPage->project = $project;
                $snapPage->snap_views = Statistic::where(
                    [
                        'video_id' => $snapPage->video_id,
                        'event'    => 'video_view',
                    ]
                )->count();
                $filtered[] = $snapPage;
            }
            return ['snaps' => $filtered, 'user' => auth()->user()];
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'error';
    }

    public function createSnapPage($id)
    {
        $user = Auth::user();
        try {
            $room  = SnapPage::where('snap_page_link', $id)->first();
            $snaps = Video::find($room->video_id);

            if ($snaps) {

                $snaps->full();
                //$relatedLatestVideo = Video::where('owner', $user->id)->where('video_type', 2)->limit(3)->orderBy('id', 'DESC')->get();
                $relatedVideosData = Video::where('owner', $user->id)->orderBy('id', 'DESC')->get();
                $relatedVideos = [];
                foreach ($relatedVideosData as $relatedVideo) {
                    $relatedVideo->updated_at_formatted = $relatedVideo->date_formatted;
                    $relatedVideos[] = $relatedVideo;
                }

                return $data = [
                    'snaps'              => $snaps,
                    //'relatedLatestVideo' => $relatedLatestVideo,
                    'relatedVideos'      => $relatedVideos,
                    'snapPageLink'       => generateSnapPageLinkId(),
                    'status'             => true,
                    'room'               => $room
                ];
            } else {
                return $data = [
                    'message' => 'Not Found',
                    'status'  => false,
                ];
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return 'error';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();

        try {

            $snapPageData = $request->validate([
                'video_id'     => 'required|integer',
                'page_name'    => 'required|string',
                'title'        => 'required|string',
                'description'  => 'required',
                'logo'         => 'required',
                'button_text'  => 'required',
                'button_link'  => 'required',
                'button_color' => 'required',
                'name'         => 'required',
                'desination'   => 'required',
                'mobile_no'    => 'integer',
                'profile_pic'  => 'required',
                'menus'        => 'required',
                'facebook_link' => 'string',
                'twitter_link' => 'string',
                'instagram_link' => 'string',
                'linkedin_link' => 'string'
            ]);

            $input['owner_id']       = $user->id;
            $input['status']         = 1;
            //$input['snap_page_link'] = generateSnapPageLinkId();

            $snapData = SnapPage::where('snap_page_link', $request->snap_page_id)->first();
            $snapData->update($snapPageData);
            if (!empty($input['allRelatedSnapId'])) {
                $relatedSnap = [];
                foreach ($input['allRelatedSnapId'] as $value) {
                    $relatedSnap['video_id']     = $value;
                    $relatedSnap['snap_page_id'] = $snapData->id;
                    RelatedSnapVideo::create($relatedSnap);
                }
            }

            if ($request->input('menus')) {
                $menus = json_encode($request->input('menus'));

                $menuData = [
                    'snap_page_id' => $snapData->id,
                    'menus'        => $menus,
                ];
                SnapPageMenu::create($menuData);
            }
            
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'error';
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        try {
            $snapPage = SnapPage::with('snapPageMenus', 'video')->where('snap_page_link', $id)->first();
            $user = User::find($snapPage->owner_id);
            $relatedSnapVideoIds = RelatedSnapVideo::where('snap_page_id', $snapPage->id)->pluck('video_id');
            $userSelectedRelatedVideos = Video::whereIn('id', $relatedSnapVideoIds)->get();

            if ($snapPage) {
                $snapPage->video->full();

                if ($user->id == $snapPage['owner_id']) {

                    $relatedVideosData = Video::where('owner', $user->id)->orderBy('id', 'DESC')->get();

                    $relatedVideos = [];
                    foreach ($relatedVideosData as $relatedVideo) {
                        $relatedVideo->updated_at_formatted = time_elapsed_string($relatedVideo->updated_at, false, $user->settings->timezone);
                        $relatedVideos[] = $relatedVideo;
                    }

                    return $data = [
                        'snapPage'                  => $snapPage,
                        'relatedVideos'             => $relatedVideos,
                        'userSelectedRelatedVideos' => $userSelectedRelatedVideos,
                        'userCanEdit'               => 'yes',
                        'status'                    => true,
                    ];

                } else{

                    return $data = [
                        'snapPage'                  => $snapPage,
                        'userSelectedRelatedVideos' => $userSelectedRelatedVideos,
                        'userCanEdit'               => 'no',
                        'status'                    => true,
                    ];
                }
                
            } else {
                return $data = [
                    'message' => 'Not Found',
                    'status'  => false,
                ];
            }

        } catch (\Exception $e) {
            return $e->getLine();
        }
        return 'error';
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $snapPage = SnapPage::find($id);

        try {
            $delete = SnapPage::deleteAllBucketFiles($snapPage->snap_page_link, $snapPage->owner_id, 'delete');

            if (isset($delete['success']) && $delete['success'] == true) {
                $snapPage->delete();
                return 'success';
            }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 'error';
    }

    public function filterSnapPage(Request $request)
    {
        $filterArray = json_decode(json_encode($request->filter), true);
        return SnapPage::filterSnapPageData($filterArray);
    }

    public function filterRelatedVideo(Request $request)
    {
        $filterArray = json_decode(json_encode($request->filter), true);
        return Video::filterVideos($filterArray);
    }

    public function duplicate(Request $request)
    {
        $duplicate = SnapPage::copySnapPage($request->all());
        return response($duplicate);
    }

    public function uploadImage(Request $request)
    {
        if ($request->file('file')) {

            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp',
            ]);
     
            $user = Auth::user();

            $file      = $_FILES['file'];
            $fileError = $file['error'];
            $fileTmp   = $request->file('file');
            $fileSize  = $request->file('file')->getSize();
            $fileName  = str_random(32) . '_' . $request->file('file')->getClientOriginalName();
            $fileExt   = $request->file('file')->getClientOriginalExtension();

            if ($fileError === 0) {

                $ownerFolder = generate_owner_folder_name($user->email);

                $type = $request->input('type');
                $snapPageLink = $request->input('snapPageLink');

                if ( $request->input('uploadType') == 'update' ) {
                    
                    $delete = SnapPage::deleteAllBucketFiles($snapPageLink, $user->id, $type);
                    if (isset($delete['success']) && $delete['success'] == true) {
                        try {
                            $fileKey = "{$ownerFolder}/{$snapPageLink}/snap-page-{$type}/{$fileName}";
                            return SnapPage::uploadImageToBucket($fileKey, $fileTmp, $fileSize);

                        } catch (\Exception $exception) {
                            return response(['success' => false, 'message' => $exception->getMessage()], $exception->getCode());
                        }
                    }

                } elseif ($request->input('uploadType') == 'add') {

                    try {
                        $fileKey = "{$ownerFolder}/{$snapPageLink}/snap-page-{$type}/{$fileName}";
                        return SnapPage::uploadImageToBucket($fileKey, $fileTmp, $fileSize);

                    } catch (\Exception $exception) {
                        return response(['success' => false, 'message' => $exception->getMessage()], $exception->getCode());
                    }
                }
            }
            return response(['message' => 'fail', 'error' => $fileError], 200);
        }
    }

    public function updateSnapPage(Request $request)
    {
        //return $request;
        $input = $request->all();
        $user = Auth::user();

        try {

            $snapPage = SnapPage::find($request->id);

            // $request->validate([
            //     'page_name'    => 'required|string',
            //     'title'        => 'required|string',
            //     'description'  => 'required',
            //     'logo'         => 'required',
            //     'call_menu'    => 'required',
            //     'button_text'  => 'required',
            //     'button_link'  => 'required',
            //     'button_color' => 'required',
            //     'name'         => 'required',
            //     'desination'   => 'required',
            //     'mobile_no'    => 'required',
            //     'profile_pic'  => 'required',
            //     'menus'        => 'required',
            // ]);

            $snapPage->update($input);

            RelatedSnapVideo::where('snap_page_id', $request->id)->delete();

            if (!empty($input['allRelatedSnapId'])) {
                $relatedSnap = [];     
                foreach ($input['allRelatedSnapId'] as $value) {
                    $relatedSnap['video_id']     = $value;
                    $relatedSnap['snap_page_id'] = $snapPage->id;
                    RelatedSnapVideo::create($relatedSnap);
                }
            }

            if ($request->input('menus')) {
                $menus = json_encode($request->input('menus'));

                $menuData = [
                    'menus' => $menus,
                ];
                SnapPageMenu::where('snap_page_id', $request->id)->update($menuData);
            }
            
            return 'success';
        } catch (\Exception $e) {
            return $e->getLine();
        }
    }
    
}
