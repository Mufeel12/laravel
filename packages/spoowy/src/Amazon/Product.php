<?php namespace Spoowy\Amazon;

use Spoowy\BaseModel;

/**
 * Class Product
 *
 * Amazon product model
 *
 * @package Spoowy\Amazon
 */
class Product extends BaseModel
{
    protected $attributes = [
        'image_url' => '',
        'rating' => '',
        'price' => '',
        'title' => '',
        'url'   => '',
        'description' => '',
    ];
}