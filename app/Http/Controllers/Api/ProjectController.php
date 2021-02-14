<?php

namespace App\Http\Controllers\Api;

use App\Experiment\ThumbnailClickCount;
use App\Experiment\ThumbnailExperiment;
use App\Http\Controllers\Controller;
use App\Project;
use App\Statistic;
use App\Video;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\TutorialVideo;

class ProjectController extends Controller
{
    /**
     * Returns list of projects for user
     *
     * @return array|mixed
     */
    public function index(Request $request)
    {
        $team = $request->user()->currentTeam();

        abort_unless($request->user()->onTeam($team), 404);

        return Project::getAllForTeam(json_decode($request->filter, true), $team->id, true, $request->user()->id)->toArray();
    }

    /**
     * Stores a new project
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $team = $request->user()->currentTeam();

        abort_unless($request->user()->onTeam($team), 404);

        $title = $request->input('project_title');
        if (empty($title)) {
            throw new \Exception('The title field is required.');
        }

        /**
         * Create project
         */
        $project = new Project();
        $project = $project->fill([
            'project_id' => generate_project_unique_id(),
            'title' => $title,
            'private' => ($request->input('is_private', 'false') == 'true' ? 1 : 0),
            'team' => $team->id,
            'owner' => $request->user()->id,
            'archived' => 0
        ]);

        $project->save();
        addToLog(['user_id'=>$request->user()->id,
                  'activity_type'=>'create_project',
                  'subject'=>"Created a Project: <span class='activity-content'>$title</span>"
                  ]); 
                    
        if ($project)
            return $project;
            return 'error';
    }

    /**
     * Display specified source
     *
     * @param Request $request
     * @return array|void
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $team = $request->user()->currentTeam();
        $snap = $request->snap;
        abort_unless($request->user()->onTeam($team), 404);

        // Get the project
        $id = $request->input('id');

        $project = Project::with('access')->where(['project_id' => $id]);
        if(is_numeric($id)){
            $project = $project->orWhere(['id' => $id]);
        }
        $project = $project->first();
        if (!$project)
            abort(404);

        $user->isAdmin = $project->isAdmin();
        $user->freeSpace = $user->freeSpace;

        $highlightUpload = $request->input('highlight_upload');

        $project_videos = $project->videos([], $snap ? true : false);

        foreach ($project_videos as $i => $video){
            $count = Statistic::where([
                'domain' => config('app.site_domain'),
                'project_id' => $project->id,
                'video_id' => $video->id,
                'event' => 'video_view'
            ])
                ->where(function ($q) {
                    $q->where('statistics.watch_start', '<>', '0')
                        ->orWhere('statistics.watch_end', '<>', '0');
                })
                ->where('statistics.watch_end', '<>', '0')
                ->groupBy('watch_session_id')->get()->count();
            $project_videos[$i]['views_count'] = $count;
            $project_videos[$i]['embed_url'] = $video->embed_url;
        }

        return [
            'project' => $project,
            'user' => $user,
            'collaborators' => $team->users,
            'videos' => $project_videos,
            'highlightUpload' => $highlightUpload,
        ];
    }

    /**
     * Updates a resource
     *
     * @param Request $request
     * @return array|mixed
     */
    public function update(Request $request)
    {
        $team = $request->user()->currentTeam();

        abort_unless($request->user()->onTeam($team), 404);

        $project = json_decode($request->input('project'), true);
        if ($project) {
            $projectEntry = Project::find($project['id']);
            $projectEntry->update(collect($project)->toArray());
            return $projectEntry;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $team = $request->user()->currentTeam();

        $project = is_numeric($id)
            ? Project::findOrFail($id)
            : Project::where('project_id', $id)->firstOrFail();

        abort_unless(($request->user()->onTeam($team) &&
            ($project->isAdmin() || $request->user()->ownsTeam($team))), 404);
        addToLog(['user_id'=>$request->user()->id,
        'activity_type'=>'create_project',
        'subject'=>"Created a Project: <span class='activity-content'>$project->title</span>"
        ]);
        $project->delete(); // Remove project
         
        return 'success';
    }

    public function getVideos(Request $request, $id)
    {
        if ($id != 'all') {
            $project = is_numeric($id)
            ? Project::findOrFail($id)
            : Project::where('project_id', $id)->firstOrFail();
            abort_unless($request->user()->onTeam($project->team()), 404);
            return $project->videos(json_decode($request->filter, true));
        } else {
            return Video::filterVideos(json_decode($request->filter), true);
        }
    }

    /**
     * Moves/duplicates a video to another project
     *
     * @param Request $request
     * @return mixed
     */
    public function moveVideo(Request $request)
    {
        $videoId = $request->input('id');
        $newProjectId = $request->input('project_id');

        $video = Video::find($videoId);

        abort_unless($request->user()->onTeam($video->team()), 404);

        if (count($video) > 0) {
            $video->project = $newProjectId;
            $video->save();
        }
        return $video;

    }

    public function getProjectsExcept(Request $request, $id)
    {
        $currentProjectId = $id;
        if (empty($currentProjectId))
            return redirect(404);

        $projects = [];

        $currentProject = false;
        // Get all projects with videos
        $projectsUserIsInvolvedIn = Project::getAllForTeam($request->user()->currentTeam()->id);

        foreach ($projectsUserIsInvolvedIn as $project) {

            $project = Project::find($project->id);
            $project->videos = $project->videos();

            // Do not add current project to project selection
            if ($project->id != $currentProjectId)
                $projects[] = $project;

            // set the thumbnail right for videos
            $i = 0;
            foreach ($project->videos as $key => $value) {
                if ($i == 0) {
                    $project->is_first = true;
                    $i = $i++;
                }
                // todo: increase number
                $project->videos[$key]->title = smart_truncate($project->videos[$key]->title, 0, 16);
                // Todo remove localhost
                $project->videos[$key]->thumbnail = \Bkwld\Croppa\Facade::url($project->videos[$key]->thumbnail, 130, 75);
                $project->videos[$key]->updated_at_in_time_elapsed = time_elapsed_string($project->videos[$key]->updated_at);
            }

            if ($project->id == $currentProjectId) {
                $currentProject = $project;
            }
        }

        if (empty($currentProject))
            return redirect(404);

        return $projects;
    }

    public function projectDetails($id)
    {
        $project = DB::table('projects')->where('id', $id)->first();
        return response()->json($project);
    }

    public function tutorialVideos(Request $request)
    {
        $tutorial = TutorialVideo::where('type', $request->id)->first();
        if ($tutorial) {
            $video = $tutorial->video;
            if ($video) {
                $video = $video->full();
                return response()->json(['success' => true, 'video' => $video]);
            }
        }
        return response()->json(['success' => false]);
    }

}
