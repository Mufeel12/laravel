<?php namespace Spoowy\Amazon\Parsers;

use Goutte\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class SellerAAG
 * @package Spoowy\Amazon\Parsers
 */
class SellerAAG
{

    /**
     * @var Collection
     */
    protected $items;

    /**
     * Seller title
     * @var string
     */
    protected $sellerTitle;

    /**
     * Seller ID
     * @var string
     */
    protected $sellerId;

    /**
     * Class constructor
     *
     * @param $url
     */
    public function __construct($url)
    {
        $this->items = new Collection();

        $client = new \GuzzleHttp\Client();

        try {
            // Parsing html
            $response = $client->get($url);
            $html = $response->getBody();
            $this->parse($html);

        } catch (RequestException $e) {
            $this->items = new Collection();
        }

        return $this;
    }


    /**
     * Parse seller page
     *
     * @param $html
     */
    public function parse($html)
    {
        $marketplace_id = $this->getMarketplaceID($html);
        $seller_id = $this->getSellerID($html);
        $this->sellerId = $seller_id;

        $crawler = new Crawler( (string) $html);
        $this->sellerTitle = $this->getSellerTitle($crawler);

        if (!empty($marketplace_id) && !empty($seller_id)) {
            $this->getProductWidget($marketplace_id, $seller_id);
        }

    }

    /**
     * Return items collection
     * @return array|Collection
     */
    public function data()
    {
        return $this->items;
    }

    public function sellerInfo()
    {
        return [
            'seller' => $this->sellerTitle,
            'url' => $this->sellerStorefrontLink($this->sellerId)
        ];
    }

    /**
     * Get seller key ID
     *
     * @param $html
     * @return mixed
     */
    private function getSellerID($html)
    {
        preg_match('/ue_pti = "(?<seller_id>\w+)"/', $html, $matches);

        if (array_key_exists('seller_id', $matches)) {
            return $matches['seller_id'];
        }
    }

    /**
     * Seller storefront formatted link
     *
     * @param $seller_id
     * @return string
     */
    private function sellerStorefrontLink($seller_id)
    {
        return "http://www.amazon.com/shops/{$seller_id}";
    }

    /**
     * Get marketplace ID
     *
     * @param $html
     * @return mixed
     */
    private function getMarketplaceID($html)
    {
        preg_match('/marketplaceID=(?<marketplace_id>\w+)/', $html, $matches);


        if (array_key_exists('marketplace_id', $matches)) {
            return $matches['marketplace_id'];
        }
    }

    /**
     * Parse seller title
     *
     * @param Crawler $crawler
     * @return string
     */
    private function getSellerTitle(Crawler $crawler)
    {
        $title = $crawler->filter('h1')->text();
        return $title;
    }

    /**
     * Get Product widget
     *
     * This widget loaded by POST ajax request at seller's page
     *
     * @param $marketplace_id
     * @param $seller_id
     */
    private function getProductWidget($marketplace_id, $seller_id)
    {
        $productWidgetUrl = 'http://www.amazon.com/gp/aag/ajax/productWidget.html';
        $client = new Client();
        $crawler = $client->request('POST', $productWidgetUrl,
            ['seller' => $seller_id, 'marketplaceID' => $marketplace_id, 'useMYI' => 1]);

        /**
         * Extract all items from response
         */
        $contentList = $crawler->filter('.shoveler-content ul');

        $this->items = new Collection($contentList->filter('.aagImgLink')->extract(['href']));
    }
}