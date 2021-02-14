<?php
/**
 * Created by PhpStorm.
 * User: erwinflaming
 * Date: 21/05/15
 * Time: 09:32
 */

if (!function_exists('array_remove_nth_element')) {
    /**
     * Removes nth element of an array
     *
     * @param $array
     * @param $n
     * @param int $offset
     * @return mixed
     */
    function array_remove_nth_element($array, $n, $offset = 0)
    {
        $arrayCount = count($array);
        if (is_decimal($n))
            $n = ceil($n);

        for ($i = $offset; $i < $arrayCount; $i += $n) {
            unset($array[$i]);
        }
        return $array;
    }
}

if (!function_exists('array_keep_only_nth_element')) {
    /**
     * Keeps only nth element of an array
     *
     * @param $array
     * @param $n
     * @param int $offset
     * @return mixed
     */
    function array_keep_only_nth_element($array, $n, $offset = 0)
    {
        uksort($array, "strnatcmp");
        $arrayCount = count($array);
        if (is_decimal($n))
            $n = ceil($n);
        for ($i = $offset; $i < $arrayCount; $i++) {
            if ($i % $n != 0 || $i == $offset)
                unset($array[$i]);
        }
        return $array;
    }
}

if (!function_exists('maybe_serialize')) {
    /**
     * Serialize data, if needed.
     *
     * @param string|array|object $data Data that might be serialized.
     * @return mixed A scalar data
     */
    function maybe_serialize($data)
    {
        if (is_array($data) || is_object($data))
            return serialize($data);

        // Double serialization is required for backward compatibility.
        // See https://core.trac.wordpress.org/ticket/12930
        if (is_serialized($data, false))
            return serialize($data);

        return $data;
    }
}


if (!function_exists('maybe_unserialize')) {
    /**
     * Unserialize value only if it was serialized.
     *
     * @param string $original Maybe unserialized original, if is needed.
     * @return mixed Unserialized data can be any type.
     */
    function maybe_unserialize($original)
    {
        if (is_serialized($original)) // don't attempt to unserialize data that wasn't serialized going in
            return @unserialize($original);
        return $original;
    }
}

if (!function_exists('is_serialized')) {
    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @param string $data Value to check to see if was serialized.
     * @param bool $strict Optional. Whether to be strict about the end of the string. Default true.
     * @return bool False if not serialized and true if it was.
     */
    function is_serialized($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace)
                return false;
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3)
                return false;
            if (false !== $brace && $brace < 4)
                return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's' :
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a' :
            case 'O' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }
}

if (!function_exists('is_serialized')) {
    /**
     * Check whether serialized data is of string type.
     *
     * @param string $data Serialized data.
     * @return bool False if not a serialized string, true if it is.
     */
    function is_serialized_string($data)
    {
        // if it isn't a string, it isn't a serialized string.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if (strlen($data) < 4) {
            return false;
        } elseif (':' !== $data[1]) {
            return false;
        } elseif (';' !== substr($data, -1)) {
            return false;
        } elseif ($data[0] !== 's') {
            return false;
        } elseif ('"' !== substr($data, -2, 1)) {
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists('strposarray')) {
    function strposarray($haystack, $needle = array(), $offset = 0)
    {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $query) {
            if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
        }
        return false;
    }
}