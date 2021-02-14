<?php

namespace App\Http\Controllers;

use App\Project;
use App\Repositories\Project\ProjectRepository;
use App\Slate;
use App\SlateTemplate;
use App\User;
use App\Video;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Browsershot\Browsershot;

class SlateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param ProjectRepository $projectRepository
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProjectRepository $projectRepository)
    {
        $slates = Slate::whereUserId(Auth::id())->get()
            ->map(function ($slate) {
                $slate->thumbnail = $slate->thumbnail;
                return $slate;
            })
            ->sortByDesc('created_at');

        return $slates;
    }

    /**
     * Index for slate templates.
     *
     * Used for show list of templates
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function templates()
    {
        $slateTemplates = SlateTemplate::all();

        return $slateTemplates->map(function ($index) {
            $index->preview = asset('img/slatePreview/' . $index->template . '.png');
            return $index;
        });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @internal param $template_id
     *
     */
    public function create(Request $request)
    {
        $template_id = $request->input('template_id');
        try {
            $template = SlateTemplate::findOrFail($template_id);
            #$template->fields = $fields;
            $template->save();
        } catch (ModelNotFoundException $e) {
            // TODO: with errors
            return redirect(route('slates.index'));
        }

        $projectRepository = new ProjectRepository(new Project());
        $user = Auth::user();

        $projects = $projectRepository->involvedProjectsByUserIdThatHaveVideos($user->getId())->map(function ($index, $key) {
            $index->videos = $index->videos;
            return $index;
        });

        // Todo: Fails if there is no project created yet.
        // Project with video needs to be created before a slate can. Maybe create a sample project?
        // Get preview video
        $previewVideo = $projects->first()->videos->first();

        $data = $template->fields;

        return view('slates.create', [
            'projects' => $projects,
            'projectsInJson' => $projects->toJson(),
            'template' => $template,
            'user' => $user,
            'title' => $request->input('slate_title', 'Create slate'),
            'previewVideo' => $previewVideo,
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Authorization for edit slate placed in StoreSlateRequest request
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $slateTemplate = SlateTemplate::findOrfail($request->input('template_id')); // TODO: Probably without fail...
        $slateFields = $request->input('fields', []);

        // Intersect slate fields with slate template fields.
        $slateFields = array_intersect_key($slateFields, $slateTemplate->fields);#array_pluck($slateTemplate->fields, ['name']);
        #$slateFields = array_intersect_key($slateFields, array_flip($slateTemplateFieldNames));

        $slate = new Slate();
        $slate->user_id = Auth::id();
        $slate->video_id = $request->input('video_id', 0);
        $slate->template_id = $request->input('template_id');
        $slate->title = $request->input('slate_title');
        $slate->fields = $request->input('fields');
        $slate->save();

        return ['slateId' => $slate->id];
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        // TODO: check for fields? Or leave this for template level check?
        $slate = Slate::findOrFail($id);
        $template = $slate->template->template;

        $user = User::find($slate->user_id);

        $params = $slate->templateFields;

        $params['slate'] = $slate;
        $params['video_id'] = $slate->video_id;
        $params['data'] = $params;

        $params['standAlone'] = true;

        if ($request->input('screenshot') == 'james') {
            $params['video_thumbnail'] = \Bkwld\Croppa\Facade::url($slate->video->thumbnail, 600, 400);
        }

        $params['user'] = $user;

        // Template loaded from slates/templates directory
        return view("slates.templates.{$template}", $params);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return array
     */
    public function edit($id)
    {
        $slate = Slate::findOrFail($id);
        #$video = Video::find($slate->video_id);
        #$user = Auth::user();
        #$project = $video->project;

        #$projectRepository = new ProjectRepository(new Project());
        #$projects = $projectRepository->involvedProjectsByUserIdThatHaveVideos($user->id);

        $template = $slate->template;

        #$data = $slate->fields;
        $fields = array_merge($template->fields, $slate->fields);

        $preRunners = [];
        if ($template->template === 'third_slate_template') {
            $preRunners = [
                'content_headline' => [
                    'key' => 'content_divider',
                    'value' => 'Content'
                ],
                'footer_title' => [
                    'key' => 'footer_divider',
                    'value' => 'Footer'
                ]
            ];
        }

        foreach ($preRunners as $key => $preRunner) {
            $fieldKeys = array_keys($fields);
            $preRunnerKey = array_search($key, $fieldKeys);
            $fields = $this->setBefore($fields, $preRunnerKey, [$preRunner['key'] => $preRunner['value']]);
        }

        $collection = collect([]);
        $slate->fields = $fields;

        return [
            'slate' => $slate,
            #'projects' => $projects,
            #'title' => 'Edit slate - ' . $slate->title,
            #'projectsInJson' => $projects->toJson(),
            'template' => $template,
            #'user' => $user,
            #'video' => $video,
            #'data' => $data
        ];
    }

    public function setBefore($array, $preRunnerKey, $value) {
        // Fields has new structure after this!
        return array_slice($array, 0, $preRunnerKey, true) 
            + $value
            + array_slice($array, $preRunnerKey, count($array)-$preRunnerKey, true);

    }

    /**
     * Update the specified resource in storage.
     *
     * Authorization rules at UpdateSlateRequest
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @internal param int $id
     *
     */
    public function update(Request $request)
    {
        $id = $request->input('slate.id');
        $slate = Slate::findOrFail($id);

        $slateTemplate = SlateTemplate::findOrfail($request->input('slate.template_id')); // TODO: Probably without fail...

        $slateFields = $request->input('slate.fields');

        #$slateTemplateFieldNames = array_pluck($slateTemplate->fields, ['name']);

        // Intersect slate fields with slate template fields.
        $slateFields = array_intersect_key($slateFields, $slateTemplate->fields);
        #$slateFields = array_intersect_key($slateFields, array_flip($slateTemplateFieldNames));

        $slate->video_id = $request->input('slate.video_id', 0);
        $slate->title = $request->input('slate.title');
        $slate->fields = $request->input('slate.fields');
        $slate->save();

        return $slate;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $slate = Slate::findOrFail($id);
        $slate->delete();

        return ['success'];
    }

    /**
     * Async slate thumbnail creation
     *
     * @param Request $request
     * @return bool
     * @throws \Exception
     */
    public function generateThumbnail(Request $request)
    {
        $slateId = $request->input('id');

        $slate = Slate::findOrFail($slateId);

        $path = $slate->getThumbnailPath();

        try {
            $browsershot = new Browsershot();
            $browsershot
                ->setURL(env('SLATE_URL') . '/' . $slateId . '?screenshot=james')
                ->setWidth(1024)
                ->setHeight(954)
                ->setTimeout(5000)
                ->save($path);
            // Remove old croppa generated thumbnails
            \Bkwld\Croppa\Facade::reset($path);
        } catch (\Exception $e) {
            \Log::emergency($e->getMessage());
            return false;
        }
    }
}
