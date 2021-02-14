<?php namespace Spoowy\Amazon\Parsers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Spoowy\Amazon\AmazonUrl;
use Spoowy\Amazon\Product;
use Spoowy\Amazon\Asin;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Detail
 *
 * Parse details page
 *
 * @package Spoowy\Amazon\Parsers
 */
class ItemDetails
{

    /**
     * @var Product
     */
    protected $item;

    public function __construct(Asin $asin)
    {
        $this->item = new Product();

        $this->parse($asin);
    }

    /**
     * Parse item
     *
     * @param Asin $asin
     * @return $this
     */
    private function parse(Asin $asin)
    {
        // Here I'm doing my magic, Amazon
        $params = [
            'Operation' => 'ItemLookup',
            'ItemId' => $asin['id'],
            'ResponseGroup' => 'Large'
        ];
        $url = AmazonUrl::aws_signed_request($params);

        $client = new Client();
        $response = $client->get($url);
        $detailsXML = simplexml_load_string($response->getBody());
        $details = $detailsXML;

        $item = $details->Items->Item;
        $title = (isset($item->ItemAttributes->Title) ? (string) $item->ItemAttributes->Title : '');
        $image = (isset($item->LargeImage->URL) ? (string) $item->LargeImage->URL : '');
        $price = (isset($item->ItemAttributes->ListPrice->FormattedPrice) ? (string) $item->ItemAttributes->ListPrice->FormattedPrice : '');
        $description = (isset($item->EditorialReviews->EditorialReview->Content) ? (string) $item->EditorialReviews->EditorialReview->Content : '');
        $description = substr(strip_tags((string) $description), 0, 197) . '...';
        $url = (isset($item->DetailPageURL) ? (string) $item->DetailPageURL : '');

        $this->item['title'] = $title;
        //$this->item['rating'] = $this->getRating($page);
        $this->item['image_url'] = $image;
        $this->item['price'] = $price;
        $this->item['description'] = $description;
        $this->item['url'] = $url;

        /*try {
            $page = $this->getWebPage($asin);
            $xml = $this->getXml($asin);
            $this->item['title'] = $page->filterXPath('//span[@id="productTitle"]')->text();
            $this->item['rating'] = $this->getRating($page);
            $this->item['image_url'] = $this->getImageUrl($xml);
            $this->item['price'] = $this->getPrice($xml);
            $this->item['description'] = str_replace("'", "\\'", substr($this->getDescription($xml), 0, 100) . '...');
            $this->item['url'] = $this->getFormattedLink($asin);
        } catch (RequestException $e) {
            $this->item['title'] = '';
            $this->item['rating'] = '';
            $this->item['image_url'] = '';
            $this->item['price'] = '';
            $this->item['url'] = '';
            $this->item['description'] = '';
        }*/

        return $this;
    }

    /**
     * Get web page contents
     *
     * @param Asin $asin
     * @return Crawler
     */
    private function getWebPage(Asin $asin)
    {
        $client = new Client();
        $response = $client->get("http://www.amazon.com/dp/{$asin->id}");
        $html = $response->getBody();

        return new Crawler((string)$html);
    }

    /**
     * Get xml item info
     *
     * @link: http://lon.gr/ata/
     *
     * @param Asin $asin
     * @return \SimpleXMLElement
     */
    private function getXml(Asin $asin)
    {
        // TODO: @simplexml_load_file(), but better disable show errors in production
        $xml = simplexml_load_file("http://lon.gr/ata/{$asin->id}");

        return $xml;
    }

    /**
     * Return parsed item
     * @return mixed
     */
    public function data()
    {
        return $this->item;
    }

    /**
     * Get image url
     *
     * @param \SimpleXMLElement $xml
     * @return string|void
     */
    private function getImageUrl($xml)
    {
        if ($xml instanceof \SimpleXMLElement) {
            return (string)$xml->Document->Links->ImgUrl;
        }
    }

    /**
     * Link to product
     *
     * @param Asin $asin
     * @return string
     */
    private function getFormattedLink(Asin $asin)
    {
        return "http://www.amazon.com/dp/{$asin->id}";
    }

    /**
     * Get item price
     *
     * @param \SimpleXMLElement $xml
     * @return string|void
     */
    private function getPrice($xml)
    {
        if ($xml instanceof \SimpleXMLElement) {
            return (string)$xml->Document->Item->Price;
        }
    }


    /**
     * Get item Note
     *
     * @param \SimpleXMLElement $xml
     * @return string
     */
    private function getDescription($xml)
    {
        if ($xml instanceof \SimpleXMLElement) {
            return (string) $xml->Document->Item->Description;
        }
    }

    /**
     * Get listing rating
     *
     * @param Crawler $crawler
     * @return mixed
     */
    private function getRating(Crawler $crawler)
    {
        $priceTitle = $crawler->filterXPath('//span[@id="acrPopover"]')->extract(['title']);

        if (isset($priceTitle[0])) {
            $raw = $crawler->filterXPath('//span[@id="acrPopover"]')->extract(['title'])[0];
            preg_match("/^(?<rating>\d\.\d)/", $raw, $matches);

            if (array_key_exists('rating', $matches)) {
                return $matches['rating'];
            }
        }
    }
}