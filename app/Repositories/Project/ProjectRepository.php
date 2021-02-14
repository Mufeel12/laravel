<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 18.11.2015
 * Time: 21:37.
 */
namespace App\Repositories\Project;

use App\Project;
use App\User;
use App\Repositories\AbstractRepository;
use Illuminate\Support\Collection;

/**
 * Class ProjectRepository.
 */
class ProjectRepository extends AbstractRepository
{
    /**
     * @var Project
     */
    protected $model;

    /**
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        parent::__construct($project);
    }
    
    public function model()
	{
	
	}
	
	/**
     * Projects by user.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function involvedProjectsByUser(User $user)
    {
        return $this->involvedProjectsByUserId($user->getKey());
    }

    /**
     * Projects by user that have one or more videos
     *
     * @param $id
     * @return Collection
     */
    public function involvedProjectsByUserIdThatHaveVideos($id)
    {
        return $this->involvedProjects($id, false, true);
    }

    /**
     * Archived projects by user.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function archivedInvolvedProjectsByUser(User $user)
    {
        return $this->archivedInvolvedProjectsByUserId($user->getKey());
    }

    /**
     * Projects by user id.
     *
     * @param int $id
     *
     * @return Collection
     */
    public function involvedProjectsByUserId($id)
    {
        return $this->involvedProjects($id, false);
    }

    /**
     * Archived projects by user id.
     *
     * @param int $id
     *
     * @return Collection
     */
    public function archivedInvolvedProjectsByUserId($id)
    {
        return $this->involvedProjects($id, true);
    }

    /**
     * Get projects by user id.
     *
     * @param int $id
     * @param bool $archived
     *
     * @param bool $mustHaveVideos
     * @return Collection
     * @internal param bool $haveVideos
     * @internal param bool $withVideos
     */
    private function involvedProjects($id, $archived, $mustHaveVideos = false)
    {
        $user = User::find($id);
        $team = $user->currentTeam();
        $projects = Project::whereTeam($team->id);
        if (!$archived)
            $projects->where('archived', 0);

        $projects = $projects->orderBy('id', 'DESC')->get();

        if ($mustHaveVideos) {
            $projects = $projects->map(function($project) {
                $newProject = $project;
                $newProject->videos = $project->videos();
                return $newProject;
            });
            $projects = $projects->filter(function($project) {
                return (count($project->videos) > 0);
            });
        }

        // Add truncated project title, this is needed in many places of this app
        return $projects->map(function ($item, $key) {
            $item->truncated_title = smart_truncate($item->project_title, 0, 17);
            $item->videos->map(function ($item, $key) {
                $item->truncated_title = smart_truncate($item->title, 0, 17);
                return $item;
            });
            return $item;
        });
    }
}
