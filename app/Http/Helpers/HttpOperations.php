<?php
/**
 * Created by PhpStorm.
 * User: erwinflaming
 * Date: 21/05/15
 * Time: 09:51
 */

if (!function_exists('post_without_wait')) {
    /**
     * Executes POST request without waiting for any return
     *
     * @param $url
     * @param $params
     * @return bool
     */
    function post_without_wait($url, $params)
    {
        $post_params = [];
        foreach ($params as $key => &$val) {
            if (is_array($val)) $val = implode(',', $val);
            $post_params[] = $key . '=' . urlencode($val);
        }
        $post_string = implode('&', $post_params);

        $parts = parse_url($url);

        if (!isset($parts['host'])) {
            return false;
        }

        $fp = fsockopen($parts['host'],
            isset($parts['port']) ? $parts['port'] : 80,
            $errno, $errstr, 30);

        $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if (isset($post_string)) $out .= $post_string;

        fwrite($fp, $out);
        fclose($fp);
    }
}

if (!function_exists('curl_get_contents')) {
    /**
     * file_get_contents alternative using curl
     *
     * @param $url
     * @param bool $as_person
     * @param bool $external
     * @return mixed
     */
    function curl_get_contents($url, $as_person = false, $external = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if ($as_person == true) {
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.22 ".
                "(KHTML, like Gecko) Ubuntu Chromium/25.0.1364.160 Chrome/25.0.1364.160 Safari/537.22");
            $cookieJar = storage_path() . '/cookies.txt';
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        }
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        //output the data to get more information.
        curl_close($ch);
        if (isset($info['redirect_url']) && !empty($info['redirect_url']))
            return $info['redirect_url'];
        return $output;
    }
}

if (!function_exists('url_exists')) {
    function url_exists($url) {
        $file = $url;
        $file_headers = @get_headers($file);
        if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
            return false;
        }
        else {
            return true;
        }
    }
}