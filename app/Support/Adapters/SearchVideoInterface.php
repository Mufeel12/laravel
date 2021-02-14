<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 30.01.2016
 * Time: 11:24
 */

namespace App\Support\Adapters;


interface SearchVideoInterface
{
    public function search($query, $params);
}