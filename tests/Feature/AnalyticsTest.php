<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     * @group analytics
     */
    public function testOptions()
    {
        $response = $this->get('/api/statistics');

        $this->assertTrue($response->isOk());

        $data = $response->json();
        $data = array_first($data);
        $this->assertTrue(array_key_exists('label', $data));
        $this->assertTrue(array_key_exists('options', $data));
        $this->assertTrue(isset($data['options'][0]));
        $this->assertTrue(array_key_exists('label', $data['options'][0]));
        $this->assertTrue(array_key_exists('value', $data['options'][0]));
    }

    /**
     * @group analytics
     */
    public function testVideo()
    {
        $response = $this->get('api/statistics/video/' . 68);

        $data = $response->json();
        dd($data);
    }
}
