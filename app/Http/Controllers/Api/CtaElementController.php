<?php

namespace App\Http\Controllers\Api;

use App\CtaElement;
use App\Video;
use Aws\Amazon\Amazon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Madcoda\Youtube\Youtube;
use ShopifyApi\Shopify\Shopify;

class CtaElementController extends Controller
{
    /**
     * Add a new cta element to a video
     *
     * @param Request $request
     * @return CtaElement|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $type = $request->input('type');
        $videoId = $request->input('videoId');
        $start_time = ceil($request->input('start_time', 0));

        $video = Video::find($videoId);

        // Set a good start time
        $video->duration_formatted = format_duration($video->duration);
        // Pre-set 10 seconds if it's at the end of the video
        if ($start_time + 10 > $video->duration && $video->duration) {
            $start_time = $video->duration - 10;
        }

        $ctaElement = new CtaElement();
        $defaultValues = $ctaElement->cta_default_values($type);
        $ctaElement->video_id = $videoId;
        $ctaElement->cta_element_type = $type;
        $ctaElement->cta_element_value = ($defaultValues);
        $ctaElement->start_time = round((int)$start_time, 0);
        $ctaElement->end_time = round((int)$start_time + 10, 0);
        $ctaElement->save();

        $ctaElement = $ctaElement::formatCtaElement($ctaElement);

        $ctaElement->cta_element_value = $defaultValues;
        return $ctaElement;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @internal param int $id
     * @return Response
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $ctaElement = CtaElement::find($id);
        $video = $ctaElement->video();

        abort_unless($request->user()->onTeam($video->team()), 404);

        if ($ctaElement) {
            $ctaElement->delete();
        }
        return 'success';
    }

    /**
     * Returns youtubeAccount as array
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function youtubeAccount(Request $request)
    {
        $yt = new Youtube([
            'key' => env('YOUTUBE_API_KEY')
        ]);
        return (array)$yt->getChannelFromURL($request->input('url'));
    }

    /**
     * Returns amazon listing as array
     *
     * @param Request $request
     * @return array
     */
    public function amazonListing(Request $request)
    {
        $amazon = new Amazon($request->input('url'));
        return $amazon->getData();
    }

    /**
     * Deprecated: Items are added manually
     *
     * Returns ebay listing as array
     *
     * @param Request $request
     * @return array
     */
    public function ebayListing(Request $request)
    {
        /*$endpoint = env('EBAY_ENDPOINT');  // URL to call
        $version = env('EBAY_VERSION');  // API version supported by your application
        $appid = env('EBAY_APP_ID');  // Replace with your own AppID
        $globalid = env('EBAY_GLOBAL_ID');  // Global ID of the eBay site you want to search (e.g., EBAY-DE)
        $query = 'BuyDig';  // You may want to supply your own query
        $safequery = urlencode($query);  // Make the query URL-friendly

        // Construct the findItemsByKeywords HTTP GET call
        $apicall = "$endpoint?";
        $apicall .= "OPERATION-NAME=findItemsByKeywords";
        $apicall .= "&SERVICE-VERSION=$version";
        $apicall .= "&SECURITY-APPNAME=$appid";
        $apicall .= "&GLOBAL-ID=$globalid";
        $apicall .= "&keywords=$safequery";
        $apicall .= "&paginationInput.entriesPerPage=10";

        // Load the call and capture the document returned by eBay API
        $resp = simplexml_load_file($apicall);

        $_data = [];
        if (!empty($resp) && $resp->ack == "Success") {
            $key = 0;
            foreach ($resp->searchResult->item as $item) {
                $_title = (array)$item->title;
                $_viewItemURL = (array)$item->viewItemURL;
                $_galleryURL = (array)$item->galleryURL;
                $_convertedCurrentPrice = (array)$item->sellingStatus->convertedCurrentPrice;
                $_data[$key]['title'] = $_title[0];
                $_data[$key]['url'] = $_viewItemURL[0];
                $_data[$key]['price'] = $_convertedCurrentPrice[0];
                $_data[$key]['button_text'] = 'Buy now for ' . $_convertedCurrentPrice[0] . ' ' . $item->sellingStatus->convertedCurrentPrice->attributes()->currencyId;
                $_data[$key]['image'] = $_galleryURL[0];
                $key++;
            }
        }
        return $_data;*/
    }

    public function shopifyListing(Request $request)
    {
        $shopify = new Shopify($request->input('url'));
        return $shopify->getData();
    }
}
