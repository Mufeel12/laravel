<?php namespace Spoowy\StockImages\Clients;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Spoowy\StockImages\Models\Image;

/**
 * Class FlickrClient
 * @package Spoowy\StockImages\Clients
 */
class FlickrClient extends BaseClient
{
    /**
     * FlickrClient constructor
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
                'method'        => 'flickr.photos.search',
                'api_key'       => $this->config['api_key'],
                'per_page'      => $this->config['per_page'],
                'tags'          => urlencode($query),
                'format'        => 'json',
                'nojsoncallback'=> '1'
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
     * Find images in json
     *
     * @param $response
     * @return array|Collection
     */
    private function findImages($response)
    {
        $json = json_decode($response);
        $collection = new Collection();

        /**
         * If json not decoded, or it invalid, or we have api error
         */
        if (empty($json) OR ! isset($json->photos) ) {
            return $collection;
        }

        foreach($json->photos->photo as $item)
        {
            $image = new Image();
            $image->url = $this->composeUrl($item);

            $collection[] = $image;
        }

        return $collection;
    }

    /**
     * Compose flickr urls fromm json response
     *
     * @see:https://www.flickr.com/services/api/misc.urls.html
     *
     * @param array $item
     * @return string
     */
    private function composeUrl($item)
    {
        //@todo: $url_template move to config?
        $url_template = 'https://farm{farm}.staticflickr.com/{server}/{id}_{secret}.jpg';

        $fields = [
            'id',
            'farm',
            'server',
            'secret'
        ];

        foreach($fields as $field) {
            $url_template = str_replace('{'.$field.'}', $item->{$field}, $url_template);
        }

        return $url_template;
    }

}