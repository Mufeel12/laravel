<?php namespace Spoowy\StockImages\Clients;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Spoowy\StockImages\Models\Image;

/**
 * Class WikimediaClient
 *
 * @package Spoowy\StockImages\Clients
 * @see:https://www.mediawiki.org/wiki/API:Allimages
 */
class WikimediaClient extends BaseClient
{

    /**
     * WikimediaClient constructor
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
                'action'    => 'query',
                'aifrom'    => urlencode($query),
                'ailimit'   => $this->config['per_page'],
                'format'    => 'json',
                'aiprop'    => 'url',
                'list'      => 'allimages'
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

    private function findImages($response)
    {
        $json = json_decode($response);
        $collection = new Collection();

        if (is_null($json)) {
            return $collection;
        }

        foreach($json->query->allimages as $item)
        {
            $image = new Image();
            $image->url = $item->url;
            $collection[] = $image;
        }

        return $collection;
    }
}