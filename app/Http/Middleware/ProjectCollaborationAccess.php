<?php

namespace App\Http\Middleware;

use Closure;
use App\Project;

class ProjectCollaborationAccess
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

        $project = Project::where(['project_id' => $request->route('project_id')])->firstOrFail();
        $granted = $user->id === $project->owner || $user->id === $project->team()->owner['id'];

        if (!$granted) {
          return \App::abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
