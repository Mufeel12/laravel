<?php

namespace Tests\Feature;

use App\CtaElement;
use App\Video;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CtaElementTest extends TestCase
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
    public function testCreate()
    {
        $this->login();
        $video = Video::find(43);
        $response = $this->post('api/ctaelement', [
            'type' => 'annotation',
            'videoId' => $video->id,
            'start_time' => 30
        ]);
        $data = $response->json();
        $this->assertTrue($response->isOk());
        $this->assertTrue(array_key_exists('video_id', $data));
        $this->assertTrue(array_key_exists('cta_element_type', $data));
        $this->assertTrue(array_key_exists('cta_element_value', $data));
        $this->assertTrue(array_key_exists('start_time', $data));
        $this->assertTrue(array_key_exists('end_time', $data));
        $this->assertTrue(array_key_exists('data', $data));
        $this->assertTrue(array_key_exists('cta_element_type_full', $data));
        $this->assertTrue($data['start_time'] == 30);
    }

    public function testDestroy()
    {
        $this->login();
        $video = Video::find(43);
        $ctaElement = CtaElement::where('video_id', 43)->first();
        $response = $this->delete('api/ctaelement', [
            'id' => $ctaElement->id
        ]);
        $this->assertTrue($response->isOk());
        $this->assertTrue($response->content() == 'success');
    }

    /*public function testYoutubeAccount()
    {
        $this->login();
        $response = $this->post('api/ctaelement/youtube', [
            'url' => 'https://www.youtube.com/user/wranglerstar'
        ]);
        $data = $response->json();
        $this->assertTrue($response->isOk());
        $this->assertTrue(array_key_exists('id', $data));
        $this->assertTrue(array_key_exists('snippet', $data));
        $this->assertTrue(array_key_exists('etag', $data));
    }

    public function testAmazonListing()
    {
        $this->login();
        $response = $this->post('api/ctaelement/amazon', [
            'url' => 'https://www.amazon.de/Scale-Universal-Innovation-Sustainability-Organisms/dp/1594205582/ref=sr_1_1?ie=UTF8&qid=1504371000&sr=8-1&keywords=Scale%3A+The+Universal+Laws+of+Growth%2C+Innovation%2C+Sustainability%2C+and+the+Pace+of+Life+in+Organisms%2C+Cities%2C+Economies%2C+and+Companies'
        ]);
        $data = $response->json();
        $this->assertTrue($response->isOk());
        $this->assertTrue(array_key_exists('url', $data));
        $this->assertTrue(array_key_exists('image', $data));
        $this->assertTrue(array_key_exists('title', $data));
        $this->assertTrue(array_key_exists('price', $data));
    }

    public function testEbayListing()
    {
        /*$this->login();
        $response = $this->post('api/ctaelement/ebay', [
            'url' => 'https://www.ebay.de/itm/Lenovo-ThinkPad-L540-Core-i5-4300M-2-6GHz-4GB-128GB-SSD-15-6-Webcam-WIN-8-1/401343471726?hash=item5d71ef606e:g:vKwAAOSwJtdaDbSX'
        ]);
        $data = $response->json();
        dd($data);
        $this->assertTrue($response->isOk());*//*
    }*/

    /*public function testWooCommerce()
    {
        $this->login();
        $response = $this->post('api/ctaelement/woocommerce', [
            'url' => 'e'
        ]);
        $data = $response->json();
        $this->assertTrue($response->isOk());
    }*/
}
