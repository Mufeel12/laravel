<?php namespace Spoowy\StockImages\Clients;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Spoowy\StockImages\Models\Image;

/**
 * Class PixabayClient
 *
 * @package Spoowy\StockImages\Clients
 */
class PixabayClient extends BaseClient
{
    /**
     * PixabayClient constructor
     *
     * @param Client $client
     * @param array $config
     */
    public function __construct(Client $client = null, $config = [])
    {
        $client = $this->defaultClient(['verify'=> false]);

        parent::__construct($client, $config);
    }

    /**
     * Search images by keyword
     *
     * @param string $query
     * @return mixed
     */
    public function search($query)
    {
        $url = $this->config['url'];
        $params = [
            'query' => [
                'username' => $this->config['username'],
                'key'      => $this->config['key'],
                'per_page' => $this->config['per_page'],
                'q' => urlencode($query),
            ],
        ];

        $response = (string) $this->request($url, $params);
        $images = $this->findImages($response);

        return $images;
    }

    /**
     * Make http request to API
     *
     * @param $url
     * @param $params
     * @return mixed
     */
    public function request($url, $params)
    {
        $res = $this->httpClient->get($url, $params);

        return $res->getBody();
    }

    /**
     * Get images from json response
     *
     * @param $response
     * @return Collection
     */
    private function findImages($response)
    {
        $json = json_decode((string) $response);

        $collection = new Collection();


        if ( empty($json) ) {
            return $collection;
        }

        foreach($json->hits as $item)
        {
            $image = new Image();
            $image->url = $item->webformatURL;
            $collection[] = $image;
        }

        return $collection;
    }
}