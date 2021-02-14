<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 08.07.2015
 * Time: 12:28
 */

namespace Spoowy\StockImages\Clients;

use GuzzleHttp\Client;

/**
 * Class BaseClient
 * @package Spoowy\StockImages\Clients
 */
abstract class BaseClient implements ApiClientInterface
{

    /**
     * Config
     * @var array
     */
    protected $config;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * Class constructor
     *
     * @param Client $client
     * @param array $config
     */
    public function __construct(Client $client = null, $config = [])
    {
        $this->httpClient = $client ?: new Client();
        $this->config = $config;
    }

    /**
     * @param array $options
     * @return Client
     */
    protected function defaultClient($options = [])
    {
        return new Client($options);
    }

    /**
     * Get http client
     *
     * @return Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }


    /**
     * Set http client
     *
     * @param Client $httpClient
     * @return void
     */
    public function setHttpClient(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

}