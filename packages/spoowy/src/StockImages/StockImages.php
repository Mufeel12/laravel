<?php

namespace Spoowy\StockImages;

use Illuminate\Support\Collection;
use Spoowy\StockImages\Exceptions\ImageBayException;

/**
 * Class ImageBay
 * @package Spoowy\StockImages
 */
class StockImages
{
    /**
     * Configuration
     * @var array
     */
    protected static $config;


    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        self::$config = $config;
    }

    /**
     * Search images
     *
     * @param $query
     * @param $sources
     * @return Collection
     */
    public function search($query, $sources = [])
    {
        $result = new Collection();

        if (empty($sources)) {
            $sources = array_keys(self::$config);
        }

        /**
         * Loop all sources from config file
         */
        foreach ($sources as $source) {
            $client = self::make($source);
            $items = $client->search($query);
            $result = $result->merge($items);
        }

        return $result;
    }

    /**
     * ImageBay client factory
     *
     * @param $client_name
     * @param array $config
     * @return mixed
     * @throws ImageBayException
     */
    public static function make($client_name, $config = [])
    {
        $client_type = ucfirst($client_name);
        $class = __NAMESPACE__.'\\Clients\\'.$client_type.'Client';

        if(class_exists($class)) {

            /**
             * Deal with config routine
             */
            if (empty($config)) {
                if(isset(self::$config[$client_name])) {
                    $config = self::$config[$client_name];
                } else {
                    throw new ImageBayException('You need provide client configuration!');
                }
            }

            return new $class(null, $config);

        } else {
            throw new ImageBayException("Class \"{$class}\" does not exists!");
        }
    }
}