<?php

namespace App\Http\Middleware;

use Closure;
use App\Project;

class ProjectAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = $request->user();
        $id = $request->input('id');
        // info(is_numeric('df65dft4d'));
        // info(is_numeric($id));
        $project = Project::with('access')->where(['project_id' => $id]);
        if(is_numeric($id)){
            $project = $project->orWhere(['id' => $id]);
        }
        $project = $project->first();

        if (is_null($project)) {
            return response(['success' => false, 'message' => 'Project not found'], 404);
        }
        $isOwner = $user->id === $project->owner || $user->id === $project->team()->owner['id'];
        $isCollaborator = $project->access->firstWhere('id', $user->id);
         
        if (!$isOwner && !$isCollaborator) {
          return response(['success' => false, 'message' => 'Access forbidden'], 403);
        }

        return $next($request);
    }
}
