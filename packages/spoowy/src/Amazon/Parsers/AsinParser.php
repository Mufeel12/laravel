<?php namespace Spoowy\Amazon\Parsers;

use Illuminate\Support\Collection;
use Spoowy\Amazon\Asin;


/**
 * Class Asin
 * @package Spoowy\Amazon\Parsers
 */
class AsinParser
{

    /**
     * Parse url for ASIN
     *
     * @param $url
     * @return mixed
     */
    public static function parse($url)
    {
        // ASIN url regex
        $asinRegex = '/(?:dp|o|gp|gp\/product|-)\/(?<asin>B[0-9]{2}[0-9A-Z]{7}|[0-9]{9}(?:X|[0-9]))/';

        preg_match($asinRegex, $url, $matches);

        // Url is not item details link
        if (!isset($matches['asin'])) {
            $class = new static();
            $collection = $class->getSellerData($url);

            return $collection;
        }

        $asin = new Asin();
        $asin->id = $matches['asin'];

        return $asin;
    }

    /**
     * Parse seller page and return items ASIN's
     *
     * @param $url
     * @return Collection
     */
    private function getSellerData($url)
    {
        $seller = new SellerAAG($url);
        $urls = $seller->data();
        $info = $seller->sellerInfo();

        $collection = new Collection();

        foreach ($urls->toArray() as $url) {
            $asin = self::parse($url);
            $collection[] = $asin;
        }

        return new Collection(array_merge($info, [
            'numbers' => $collection
        ]));
    }
}