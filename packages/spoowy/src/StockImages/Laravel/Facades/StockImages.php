<?php

namespace Spoowy\StockImages\Laravel\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * Class ImageHousting
 * @package Spoowy\StockImages\Laravel\Facades
 */
class StockImages extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stockimages';
    }

    /**
     * Search images
     *
     * @param   string $query
     * @param   array$sources
     * @return  Collection
     */
    public static function search($query, $sources = [])
    {
        return static::$app['stockimages']->search($query, $sources);
    }
}