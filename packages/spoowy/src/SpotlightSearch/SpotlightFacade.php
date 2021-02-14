<?php namespace Spoowy\SpotlightSearch;

use Illuminate\Support\Facades\Facade;

/**
 * Class SpotlightFacade
 * @package Spoowy\SpotlightSearch
 */
class SpotlightFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'spotlight';
    }

    /**
     * Search
     *
     * @param $query
     * @return mixed
     */
    public static function search($query)
    {
        return static::$app['spotlight']->search($query);
    }

    /**
     * Suggestion search
     *
     * @param $query
     * @return mixed
     */
    public static function suggestion($query)
    {
        return static::$app['spotlight']->suggestion($query);
    }

}