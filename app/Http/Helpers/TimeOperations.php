<?php
/**
 * Created by PhpStorm.
 * User: erwinflaming
 * Date: 25/05/15
 * Time: 12:35
 */

if (!function_exists('time_elapsed_string')) {
    /**
     * Returns time elapsed in string
     *
     * @param $datetime
     * @param bool $full
     * @param string $timezone
     * @return bool
     * @throws
     */
    function time_elapsed_string($datetime, $full = false, $timezone = 'EST') {
	    $now = new DateTime('now', new DateTimeZone($timezone));
	    $ago = new DateTime($datetime, new DateTimeZone($timezone));
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (!function_exists('format_duration')) {
    /**
     * Converts video duration from seconds to readable format HH:mm:ss
     *
     * @param $seconds
     * @return string
     */
    function format_duration($seconds) {
        if ($seconds >= 3600)
            $duration_formatted = sprintf('%02d:%02d:%02d', ($seconds / 3600), ($seconds / 60 % 60), $seconds % 60);
        else
            $duration_formatted = sprintf('%02d:%02d', ($seconds / 60 % 60), $seconds % 60);
        if ($duration_formatted[0] == "0")
            $duration_formatted = substr($duration_formatted, 1);
        return $duration_formatted;
    }
}

if (!function_exists('time_to_seconds')) {
    /**
     * Converts video time to seconds
     *
     * @param $time
     * @return int
     */
    function time_to_seconds($time) {
        $seconds = strtotime("1970-01-01 $time UTC");
        return $seconds;
    }
}


if (!function_exists('time_according_to_user_time_zone')) {

    function time_according_to_user_time_zone($date, $timezone = 'America/New_York')
    {

        $user = auth()->user();

        if($user == null){
            $date = date('Y-m-d H:i:s', strtotime($date));
            return $date;
        }

        $settings = \App\UserSettings::where(['user_id' => $user['id']])->first();
        if (!is_null($settings) && !is_null($settings->timezone)){
            $timezone = $settings->timezone;
        }

        $datetime = new DateTime($date);
        $user_timezone = new DateTimeZone($timezone);
        $datetime->setTimezone($user_timezone);
        $datetime_date = json_decode(json_encode($datetime), true);
        $date = date('Y-m-d H:i:s', strtotime($datetime_date['date']));
        return $date;
    }
}

if (!function_exists('get_seconds_from_time_string')) {

    function get_seconds_from_time_string($time)
    {
        $timeArr = array_reverse(preg_split('%\:|\.%', $time));
        $secconds = 0;
        array_key_exists('0', $timeArr) ? $secconds += $timeArr[0] : null;
        array_key_exists('1', $timeArr) ? $secconds += ($timeArr[1] * 60) : null;
        array_key_exists('2', $timeArr) ? $secconds += ($timeArr[2] * 60 * 60) : null;

        return $secconds;
    }
}