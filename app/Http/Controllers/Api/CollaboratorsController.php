<?php

namespace App\Http\Controllers\Api;

use App\Project;
use App\Team;
use App\User;
use App\ProjectAccess;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Spark\Events\Teams\TeamMemberRemoved;
use Laravel\Spark\Interactions\Settings\Teams\SendInvitation;
use Laravel\Spark\Repositories\TeamRepository;
use Laravel\Spark\Spark;

class CollaboratorsController extends Controller
{
    /**
     * List collaborators for project
     *
     * @param   Request $request
     * @param   int $project_id
     * @return  Response
     */
    protected $roles = [
        'sub-user' => 'Sub-user',
        'subuser' => 'Sub-user',
        'owner' => 'Account owner',
        'project-owner' => 'Project owner',
        'projectowner' => 'Project owner'
    ];

    protected function isRole($user, $role)
    {
        switch ($role) {
            case 'sub-user':
            case 'subuser':
                return $user['pivot']['role'] === 'subuser' || $user['pivot']['role'] === 'sub-user';
            case 'owner':
                return $user['pivot']['role'] === 'owner';
            default:
                return false;
        }
    }

    public function index(Request $request, $project_id) {
        $project = Project::where(['project_id' => $project_id])->firstOrFail();
        $user_ids = $project->access->pluck('id');
        $user_ids->push($project->team()->owner->id);
        $user_ids->push($project->owner);

        $users = $project
            ->team()
            ->users
            ->whereIn('id', $user_ids);

        $result['total'] = $project->team()->users->count();

        $test = $users->map(function ($item) use ($project) {
            $item['role_text'] = $this->roles[$item['pivot']['role']];

            if ($project->owner === $item->id && $this->isRole($item, 'owner')) {
                $item['role_text'] = [
                    $item['role_text'],
                    $this->roles['project-owner']
                ];
                $item['pivot']['role'] = [
                    $item['pivot']['role'],
                    'project-owner'
                ];
            } else if ($project->owner === $item->id && $this->isRole($item, 'subuser')) {
                $item['role_text'] = $this->roles['project-owner'];
                $item['pivot']['role'] = 'project-owner';
            }

            return $item;
        })->toArray();

        $result['users'] = array_values($test);
        return $result;
    }

    public function allTeamUsers(Request $request, $project_id)
    {
        $searchQuery = $request->input('search');
        $project = Project::where(['project_id' => $project_id])->firstOrFail();

        $collection = $request->has('search') && strlen($searchQuery) > 0
            ? $project->team()->users
                ->filter(function ($item) use ($searchQuery) {
                    return stripos($item->name, $searchQuery) !== false || stripos($item->email, $searchQuery) !== false;
                })
            : $project->team()->users;

        $result['total'] = $project->team()->users->count();
        $result['users'] = $collection->map(function ($item) use ($project) {
            $item['role_text'] = $this->roles[$item['pivot']['role']];

            if ($project->owner === $item->id && $this->isRole($item, 'owner')) {
                $item['role_text'] = [
                    $item['role_text'],
                    $this->roles['project-owner']
                ];
                $item['pivot']['role'] = [
                    $item['pivot']['role'],
                    'project-owner'
                ];
            } else if ($project->owner === $item->id && $this->isRole($item, 'subuser')) {
                $item['role_text'] = $this->roles['project-owner'];
                $item['pivot']['role'] = 'project-owner';
            }

            $item['project_access'] = $project->access->firstWhere('id', $item['id']) ? true : false;

            if ($project->owner === $item['id'] || $item['pivot']['role'] === 'owner') {
                $item['project_access'] = true;
            }
            return $item;
        })->toArray();

        return $result;
    }

    /**
     * Add user to collaboration and set permissions
     *
     * @param   Request $request
     * @param   int $project_id
     * @return  Response
     */
    public function store(Request $request, $project_id)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $email = $request->input('email');

        $project = Project::where(['project_id' => $project_id])->firstOrFail();
        $team = $project->team();

        abort_unless($request->user()->ownsTeam($team), 404);

        Spark::interact(SendInvitation::class, [$team, $email]);

        return User::where('email', $email)->first();
    }

    /**
     * Display collaborator
     *
     * @param   Request $request
     * @param   int $project_id
     * @param   int $collaborator_id
     * @return  Response
     */
    public function show(Request $request, $project_id, $collaborator_id)
    {
        $collaboration = ProjectUsersInvolved::project($project_id)
            ->collaborator($collaborator_id)
            ->with('user')
            ->first();

        // Collaborator not found
        if (is_null($collaboration)) {
            abort(404);
        }

        return $collaboration;
    }

    /**
     * Update user collaboration info
     *
     * @param   Request $request
     * @param   int $project_id
     * @param   int $collaborator_id
     * @return Response
     */
    public function update(Request $request, $project_id, $collaborator_id)
    {

        $this->validate($request, [
            'permission' => 'required|string'
        ]);

        $permission = $request->input('permission');

        $permissions = ProjectUsersInvolved::collaboratorPermissions($project_id, $permission);

        $collaboration = ProjectUsersInvolved::project($project_id)
            ->collaborator($collaborator_id)
            ->first();

        // Collaborator resource not found
        if (is_null($collaboration)) {
            abort(404);
        }

        $user = $collaboration->user;

        /**
         * You can't change permission of project owner
         */
        if ($user->id === $collaboration->is_creator_of_project) {
            abort(403);
        }

        $user->permissions = $permissions;
        $user->save();

        $collaboration = ProjectUsersInvolved::collaboratorsCleanUp($collaboration);

        return $collaboration;
    }

    /**
     * Remove user from collaboration
     *
     * @param   int $project_id
     * @param   int $collaborator_id
     * @return  Response
     */
    public function destroy(Request $request, $teamId, $project_id, $collaborator_id)
    {
        $team = Spark::interact(TeamRepository::class . '@find', [$teamId]);
        $team->users()->detach($collaborator_id);

        $member = User::findOrFail($collaborator_id);

        event(new TeamMemberRemoved($team, $member));

        return ['message' => 'ok'];
    }

    public function setAccess(Request $request, $project_id) {
        $searchQuery = $request->input('search');

        $project = Project::where(['project_id' => $project_id])->firstOrFail();
        if ($project->team()->owner->id !== $request->user()->id && $project->owner !== $request->user()->id) {
            $hasAccess = $project->access()->where('user_id', $request->user()->id)->firstOrFail();
        }

        $data = $request->input('data');

        $dataForDelete = collect($data)->filter(function ($item) use ($project, $request) {
            return $item['project_access'] === false
                && $item['project_id'] === $project->project_id
                && (int) $item['user_id'] !== $request->user()->id
                && (int) $item['user_id'] !== $project->owner
                && (int) $item['user_id'] !== $project->team()->owner->id;
        })->map(function ($item) use ($project){
            return [
                'project_id' => $project->id,
                'user_id' => (int) $item['user_id'],
            ];
        })->toArray();

        $dataForAdd = collect($data)->filter(function ($item) use ($project, $request) {
            return $item['project_access'] === true
                && $item['project_id'] === $project->project_id
                && (int) $item['user_id'] !== $request->user()->id
                && (int) $item['user_id'] !== $project->owner
                && (int) $item['user_id'] !== $project->team()->owner->id;
        })->map(function ($item) use ($project){
            return [
                'project_id' => $project->id,
                'user_id' => (int) $item['user_id'],
            ];
        });
         
        $dataForAdd->each( function ($item) use($request,$project) {
            $r = ProjectAccess::firstOrCreate($item);
            if($r->wasRecentlyCreated){
                $member = User::findOrFail($r->user_id);
                addToLog(['user_id'=>$request->user()->id,
                'activity_type'=>'create_collaborate_project',
                'subject'=>"Added a new collaborator <span class='activity-content'>$member->email</span> to: <span class='activity-content'>$project->title</span>"
                ]);    
            }
        });

        if (count($dataForDelete)) {
            $this->removeCollaborationActivity($dataForDelete,$request,$project);
          $r =  ProjectAccess::bulkDelete($dataForDelete);
          
        }
        
        return $data;
    }
    function removeCollaborationActivity($dataForDelete,$request,$project){
        foreach($dataForDelete as $item){
           
            $r = ProjectAccess::where($item)->first();
            if($r!=NULl){
                $member = User::findOrFail($r->user_id);
                addToLog(['user_id'=>$request->user()->id,
                'activity_type'=>'remove_collaborate_project',
                'subject'=>"Removed a collaborator <span class='activity-content'>$member->email</span> from: <span class='activity-content'>$project->title</span>"
                ]);  
            }
            
    }
         
    }
    public function checkAccess(Request $request, $project_id) {
        $user = $request->user();
        $project = Project::where(['project_id' => $project_id])->firstOrFail();
        $granted = $user->id === $project->owner || $user->id === $project->team()->owner['id'];

        return [ 'granted' => $granted ];
    }
}
