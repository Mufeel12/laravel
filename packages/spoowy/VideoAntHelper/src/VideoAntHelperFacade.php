<?php
/**
 * Created by PhpStorm.
 * User: erwinflaming
 * Date: 25/10/15
 * Time: 16:25
 */

namespace Spoowy\VideoAntHelper;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;

class VideoAntHelperFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'videoanthelper';
    }

    /**
     * Search
     *
     * @param $query
     * @return mixed
     */
    public static function putOutStuff($query)
    {
        return static::$app['videoanthelper']->echoPhrase($query);
    }
}