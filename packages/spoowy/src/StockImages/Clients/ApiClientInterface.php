<?php

namespace Spoowy\StockImages\Clients;

/**
 * Interface ApiClientInterface
 * @package Spoowy\StockImages\Clients
 */
interface ApiClientInterface {

    /**
     * Search images by keyword
     *
     * @param string $query
     * @return mixed
     */
    public function search($query);

    /**
     * Make http request to API
     *
     * @param $url
     * @param $params
     * @return mixed
     */
    public function request($url, $params);
}