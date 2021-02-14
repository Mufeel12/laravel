<?php
/**
 * Created by PhpStorm.
 * User: erwinflaming
 * Date: 05/03/15
 * Time: 17:37
 */

if (!function_exists('image_path')) {
    /**
     * Get the path to the media images folder.
     *
     * @param  string  $path
     * @return string
     */
    function image_path($path = '', $returnAsUrl = false)
    {
        if ($returnAsUrl)
            return asset('data/images' . ($path ? '/' . $path : $path));
        return public_path('data/images' . ($path ? '/' . $path : $path));
    }
}

if (!function_exists('thumbnail_path')) {
    /**
     * Get the path to the media images folder.
     *
     * @param  string  $path
     * @return string
     */
    function thumbnail_path($path = '', $returnAsUrl = false)
    {
        if ($returnAsUrl)
            return asset('data/videos/thumbnails' . ($path ? '/' . $path : $path));
        return public_path('data/videos/thumbnails' . ($path ? '/' . $path : $path));
    }
}

if (!function_exists('slates_path')) {
    /**
     * Get the path to the media images folder.
     *
     * @param  string  $path
     * @return string
     */
    function slates_path($path = '', $returnAsUrl = false)
    {
        if ($returnAsUrl)
            return asset('data/slates' . ($path ? '/' . $path : $path));
        return public_path('data/slates' . ($path ? '/' . $path : $path));
    }
}

if(!function_exists('doApiRequest')){

    /**
     * Call encoding API
     *
     * @param  Array  $data
     * @return Array
     */
    function doApiRequest($data)
    {
        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => json_encode($data),
                'header' => "Content-Type: application/json\r\n" . "Accept: application/json\r\n",
            ),
        );

        $url = "https://encoding.bigcommand.com/api/v1.0/api.php";
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result, true);

    }

}


if(!function_exists('callForensicAPI')){

    function callForensicAPI($data)
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post('https://forensic.bigcommand.com:443/Adilo_orchestrator-f289890C@73209cB6d01cccd7/watermarkVid/',
            [
                'json' => $data,
                'verify' =>false,
            ]
        );

        $body = json_decode((string)$response->getBody());

        return $body;
    }

}
