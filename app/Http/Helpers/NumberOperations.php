<?php
/**
 * Created by PhpStorm.
 * User: erwinflaming
 * Date: 21/05/15
 * Time: 09:37
 */

if (!function_exists('is_decimal')) {
    /**
     * Returns true if value is decimal
     *
     * @param $val
     * @return bool
     */
    function is_decimal($val)
    {
        return is_numeric( $val ) && floor( $val ) != $val;
    }
}

if (!function_exists('format_bytes')) {
    /**
     * Returns formatted bytes
     *
     * @param $size
     * @param int $precision
     * @return string
     */
    function format_bytes($size, $precision = 2) {
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');

        try {
            return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
        } catch (\ErrorException $e) {
            return '0B';
        }
    }
}

