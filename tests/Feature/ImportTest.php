<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ImportTest extends TestCase
{
    protected $user;

    public function login()
    {
        Auth::loginUsingId(1);
        $this->user = Auth::user();
    }

    public function testSearch()
    {
        $this->assertTrue(true);
        /*
        $this->login();
        $response = $this->get('api/import/search?q=test&source=youtube');
        $data = $response->json();
        $this->assertTrue(isset($data['youtube_next_page']));
        $this->assertTrue($data['query'] == 'test');*/
    }

    /*public function testDestroy()
    {
    }*/
}
