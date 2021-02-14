<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.10.2015
 * Time: 10:54
 */

namespace App\Support\Youtube;

use Madcoda\Youtube\Youtube;

class YoutubeClient extends Youtube
{
    /**
     * Get api
     *
     * @param $url
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function api_get($url, $params)
    {

        //set the youtube key
        $params['key'] = $this->youtube_key;

        //boilerplates for CURL
        $tuCurl = curl_init();

        // Don't verify SSL certificate
        curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($tuCurl, CURLOPT_URL, $url . (strpos($url, '?') === false ? '?' : '') . http_build_query($params));
        if (strpos($url, 'https') === false) {
            curl_setopt($tuCurl, CURLOPT_PORT, 80);
        } else {
            curl_setopt($tuCurl, CURLOPT_PORT, 443);
        }
        if ($this->referer !== null) {
            curl_setopt($tuCurl, CURLOPT_REFERER, $this->referer);
        }
        curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
        $tuData = curl_exec($tuCurl);
        if (curl_errno($tuCurl)) {
            throw new \Exception('Curl Error : ' . curl_error($tuCurl), curl_errno($tuCurl));
        }
        return $tuData;

    }

}
