<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SlateTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     * @group slate
     */
    public function testExample()
    {
        $res = $this->get('api/slates/8');
        $data = $res->json();
        dd($data);
    }
}
