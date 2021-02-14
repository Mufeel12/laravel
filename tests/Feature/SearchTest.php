<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
{
    protected $user;

    public function login()
    {
        Auth::loginUsingId(1);
        $this->user = Auth::user();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSearch()
    {
        $this->login();
        $response = $this->post('api/search', [
            'query' => 'e'
        ]);
        $data = $response->json();
        $this->assertTrue($response->isOk());
        $this->assertTrue(array_key_exists('value', $data[0]));
        $this->assertTrue(array_key_exists('type', $data[0]));
        $this->assertTrue(array_key_exists('title', $data[0]));
        $this->assertTrue(array_key_exists('icon', $data[0]));
        $this->assertTrue(array_key_exists('link', $data[0]));
    }
}
