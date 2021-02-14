<?php

namespace Spoowy;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class BaseModel
 *
 * @package Spoowy
 */
abstract class BaseModel implements \ArrayAccess, Jsonable, Arrayable
{
    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes))
        {
            return $this->attributes[$key];
        }
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->attributes)) {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Attributes to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Attributes to json
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray());
    }

    /**
     * Array access offset exists
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Array access get value
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Array access set value
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Array access unset value
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}