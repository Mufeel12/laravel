<?php

namespace Tests\Feature;

use App\Project;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    protected $user;

    /**
     * @group project
     */
    public function login()
    {
        Auth::loginUsingId(1);
        $this->user = Auth::user();
    }

    /**
     * @group project
     */
    public function testVideos()
    {
        $project = Project::find(20);
        $this->assertTrue(count($project->videos()) > 0);
    }

    /**
     * @group project
     */
    public function testTeam()
    {
        $project = Project::find(20);
        $this->assertTrue(count($project->team()) > 0);
    }

    /**
     * @group project
     */
    public function testGetAllForTeam()
    {
        $this->login();
        // Get projects for user team
        $projects = Project::getAllForTeam($this->user->currentTeam->id);
        $this->assertTrue(is_array($projects[0]->thumbnails));
    }

    /**
     * @group project
     */
    public function testIsAdmin()
    {
        $this->login();
        $this->assertTrue(Project::find(1)->isAdmin());
    }

    /*public function testLastUpdateDate()
    {

    }

    public function testVideosViews()
    {

    }*/


    /***
     * Controller
     */
    /**
     * @group project
     */
    public function testIndex()
    {
        $this->login();
        $response = $this->get('api/1/projects')->json();
        $this->assertTrue(is_array($response));
    }

    /**
     * @group project
     */
    public function testStore()
    {
        $this->login();
        $response = $this->post('api/1/projects', [
            'project_title' => 'New Project ' . str_random(4)
        ]);

        $this->assertTrue($response->isOk());
        $this->assertTrue($response->content() != 'error');
        $data = $response->json();
        $this->assertTrue($data['private'] == 0);
    }

    /**
     * @group project
     */
    public function testShow()
    {
        $this->login();
        $response = $this->get('api/1/projects/show?id=8');
        $this->assertTrue(is_array($response->json()));
    }

    /**
     * @group project
     */
    public function testUpdate()
    {
        $this->login();
        $response = $this->put('api/1/projects', ['project' => [
            'id' => 8,
            'title' => 'Jamaica'
        ]]);
        $this->assertTrue(is_array($response->json()));
    }

    /**
     * @group project
     */
    public function testDestroy()
    {
        $this->login();
        $newProject = new Project();
        $newProject->title = 'testtodelete';
        $newProject->owner = $this->user->id;
        $newProject->team = $this->user->currentTeam->id;
        $newProject->thumbnails = json_encode([]);
        $newProject->private = 0;
        $newProject->archived = 0;
        $newProject->save();

        $response = $this->delete('api/1/projects', ['id' => $newProject->id]);
        $this->assertTrue($response->content() == 'success');
    }

    /**
     * @group project
     */
    public function testMoveVideo()
    {
        $this->login();
        $response = $this->post('api/1/projects/move/video', ['id' => 30, 'project_id' => 999]);
        $data = $response->json();
        $this->assertTrue(isset($data['id']));
        $this->assertTrue(isset($data['project']));
        $this->assertTrue($data['project'] == 999);
    }
}
