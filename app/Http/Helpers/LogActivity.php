<?php
/**
 * Log the users Activity
 */


if (!function_exists('addToLog')) {
    /**
     * Returns true if value is decimal
     *
     * @param $val
     * @return bool
     */
    
   function addToLog($log)
    {

    	$log['method'] = Request::method();
    	$log['ip'] = Request::ip();
    	$log['created_at'] = date('Y-m-d H:i:s');
    	\App\ActivityLog::create($log);
    }
}

if (!function_exists('logActivityLists')) {
    /**
     * Returns true if value is decimal
     *
     * @param $val
     * @return bool
     */
   
    function logActivityLists()
    {
    	return \App\ActivityLog::latest()->get();
    }
}