<?php

namespace Tests\Feature;

use App\Video;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VideoTest extends TestCase
{
    protected $user;

    public function login()
    {
        Auth::loginUsingId(1);
        $this->user = Auth::user();
    }

    /**
     * @group repair
     */
    public function testRepairThumbs()
    {
        $videos = Video::all();
        $videos->each(function($video) {
            $video->thumbnail = str_replace('\\', '', $video->thumbnail);
            $video->save();
        });
    }

    /**
     * @group video
     */
    public function testShow()
    {
        $this->login();
        $response = $this->get('api/1/editor?id=10');
        $data = $response->json();
        $this->assertTrue(array_key_exists('video', $data));
        $this->assertTrue(array_key_exists('ctaElements', $data));
        $this->assertTrue(array_key_exists('connectedToProviders', $data));
        $this->assertTrue(array_key_exists('comments', $data));
        $this->assertTrue(array_key_exists('ctaElementTypes', $data));
        $this->assertTrue(array_key_exists('ctaLabels', $data));
        $this->assertTrue(array_key_exists('details', $data));
    }

    /**
     * @group video
     */
    public function testUpdate()
    {
        $this->login();

        $video = Video::find(23);
        $video->id = 23;
        $video->title = 'aara;';
        $video->description = 'testdescription';
        $video->path = 'https://www.youtube.com/watch?v=IdFMCyohMgo';
        $video->imported = 1;
        $video = $video->toArray();
        $video['player_options'] = [
            'autoplay' => 0,
            'speed_control' => 0,
            'playback' => 0,
            'protected' => 1,
            'password' => 'tesla',
        ];

        $response = $this->put('api/1/editor', [
            'video' => $video
        ]);

        $data = $response->json();
        $this->assertTrue(isset($data['video']));
        $this->assertTrue(isset($data['ctaElements']));
    }

    /**
     * @group description
     */
    /*public function testDefaultDescription()
    {
        $video = Video::find(23);
        $description = VideoDescription::defaultDescription($video);
        $this->assertTrue(starts_with($description, 'Special Melbourne'));
    }*/

    /**
     * @group videoshow
     */
    public function testVideoShow()
    {
        $this->login();
        $response = $this->get('api/video/show?id=60');
        $data = $response->json();
        $this->assertTrue(array_key_exists('video', $data));
        $this->assertTrue(array_key_exists('ctaElements', $data));
        $this->assertTrue(array_key_exists('ctaElementTypes', $data));
        $this->assertTrue(array_key_exists('ctaLabels', $data));
    }

    public function testUnlock()
    {
        $this->login();
        $response = $this->post('api/video/unlock', [
            'id' => 60
        ]);
        $data = $response->json();
        $this->assertTrue($response->isOk());
        $this->assertTrue(array_key_exists('video', $data));
        $this->assertTrue(array_key_exists('ctaElements', $data));
        $this->assertTrue(array_key_exists('ctaElementTypes', $data));
        $this->assertTrue(array_key_exists('ctaLabels', $data));
    }

    /**
     * @group videostore
     */
    public function testStore()
    {
        $this->login();
        $response = $this->post('api/video', [
            'project_id' => 60,
            'title' => 'Title',
            'upload_original_name' => 'imported',
            ''
        ]);
        $data = $response->json();
        $this->assertTrue($response->isOk());
        $this->assertTrue(array_key_exists('id', $data));
    }

    /**
     * @group videodestroy
     */
    public function testDestroy()
    {
        $this->login();
        $lastVideo = DB::table('videos')->orderBy('created_at', 'desc')->first();
        $response = $this->delete('api/video', [
            'id' => $lastVideo->id
        ]);
        $this->assertTrue($response->isOk());
        $this->assertTrue($response->content() == 'success');
        $this->assertTrue(Video::find($lastVideo->id) == null);
    }

    /**
     * @group videoduplicate
     */
    public function testDuplicate()
    {
        $this->login();
        $lastVideo = DB::table('videos')->orderBy('created_at', 'desc')->first();
        $response = $this->post('api/video/duplicate', [
            'id' => $lastVideo->id
        ]);
        $data = $response->json();
        $this->assertTrue($response->isOk());
        $this->assertTrue(ends_with($data['title'], ' 2'));
    }

    /**
     * @group videomove
     */
    public function testMove()
    {
        $this->login();
        $lastVideo = DB::table('videos')->orderBy('created_at', 'desc')->first();
        $response = $this->post('api/video/move', [
            'id' => $lastVideo->id,
            'project_id' => 1
        ]);
        $data = $response->json();
        $this->assertTrue($data['project'] == 1);
    }

    /*
    /**
     * @group videovimeo
     *
    public function testVimeo()
    {
        $response = $this->post('api/video/getVimeoUrl', [
            'path' => 'https://vimeo.com/133734295'
        ]);

        $this->assertTrue(strpos($response->content(), '.mp4') !== false);
    }*/

    public function testDuration()
    {
        $video = Video::find(40);
        $this->assertTrue($video->duration == 363);
        $video->duration_formatted = 0;
        $video->save();
        $this->assertTrue($video->duration_formatted == '6:03');
        $this->assertTrue($video->duration == 363);
    }

    public function testGetAllForUser()
    {
        $this->login();
        $videos = Video::getAllForUser();
        $this->assertTrue(isset($videos[0]->video_id));
    }

    public function testCtaElements()
    {
        $this->login();
        $video = Video::find(40);
        $elements = $video->ctaElements();
        $this->assertTrue(isset($elements[0]->cta_element_type));
    }

    public function testPlayerOption()
    {
        $this->login();
        $video = Video::find(40);
        $options = $video->player_options;
        $this->assertTrue(isset($options->control_visibility));
    }

    public function testDescription()
    {
        $this->login();
        $video = Video::find(23);
        $descr = $video->description();
        $this->assertTrue($descr == 'testdescription');
    }

    public function testProject()
    {
        $this->login();
        $video = Video::find(40);
        $res = $video->project();
        $this->assertTrue(isset($res->video_views_count));
    }

    /**
     * @group videoGenerateDefaultThumbnail
     */
    public function testGenerateDefaultThumbnail()
    {
        $this->login();
        $video = Video::find(23);
        $res = $video->generateDefaultThumbnail();
        $this->assertTrue(strpos($res, 'sunshine.website') !== false);
    }

    /**
     * @group videoGenerateScrumb
     */
    public function testGenerateScrumb()
    {
        $this->login();
        $video = Video::find(23);
        $res = $video->generateScrumb();
        dd($res);
        $this->assertTrue(strpos($res, 'sunshine.website') !== false);
    }
}
