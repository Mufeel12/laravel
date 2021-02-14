<?php
/**
 * Created by PhpStorm.
 * User: erwinflaming
 * Date: 26/08/15
 * Time: 12:28
 */

if (!function_exists('resource_path')) {
    function resource_path($path) {
        return public_path($path);
    }
}

if (!function_exists('startsWith')) {
    /**
     * Verifies whether $haystack starts with $needle
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
}

if (!function_exists('endsWith')) {
    /**
     * Verifies whether $haystack ends with $needle
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncates a string
     *
     * @param $str
     * @param $len
     * @param bool $doNotAddDots
     * @return string
     */
    function truncate($str, $len, $doNotAddDots = false) {
        $tail = max(0, $len-10);
        $trunk = substr($str, 0, $tail);
        if ($doNotAddDots == false)
            // Add dots
            $trunk .= strrev(preg_replace('~^..+?[\s,:]\b|^...~', '...', strrev(substr($str, $tail, $len-$tail))));
        return $trunk;
    }
}

if (!function_exists('smart_truncate')) {
    /**
     * Truncates a string, the smart way
     *
     * @param $str
     * @param $start
     * @param $width
     * @param bool $withDots
     * @return string
     */
    function smart_truncate($str, $start, $width, $withDots = true) {
        return rtrim(mb_strimwidth($str, $start, $width)) . (strlen($str) > $width && $withDots == true ? '...' : '');
    }
}

if (!function_exists('is_json')) {
    /**
     * Returns true if string is json
     *
     * @param $string
     * @return bool
     */
    function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode($plainText) {
        return strtr(base64_encode($plainText), '+/=', '-_,');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($plainText) {
        return base64_decode(strtr($plainText, '-_,', '+/='));
    }
}


if (!function_exists('generate_project_unique_id')) {
    function generate_project_unique_id() {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_';
        $result = '';
        for ($i = 0; $i < 8; $i++) {
            $result .= $characters[mt_rand(0, 62)];
        }

        $video_exist = \App\Project::where(['project_id' => $result])->first();
        if (!is_null($video_exist))
            generate_video_unique_id();

        return $result;
    }
}


if (!function_exists('generate_video_unique_id')) {
    function generate_video_unique_id() {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_';
        $result = '';
        for ($i = 0; $i < 8; $i++) {
            $result .= $characters[mt_rand(0, 62)];
        }

        $video_exist = \App\Video::where(['video_id' => $result])->first();
        if (!is_null($video_exist))
            generate_video_unique_id();

        return $result;
    }
}

if (!function_exists('generateSnapPageLinkId')) {
    function generateSnapPageLinkId() {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_';
        $result = '';
        for ($i = 0; $i < 8; $i++) {
            $result .= $characters[mt_rand(0, 62)];
        }

        $video_exist = \App\SnapPage::where(['snap_page_link' => $result])->first();
        if (!is_null($video_exist))
            generateSnapPageLinkId();

        return $result;
    }
}

if (!function_exists('generate_owner_folder_name')) {
    function generate_owner_folder_name($email) {
        $email = str_replace('.', '', $email);
        $email = str_replace('@', '', $email);
        return $email;
    }
}


