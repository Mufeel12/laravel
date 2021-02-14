<?php

namespace Aws\Amazon;

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;

class Amazon
{

    protected $url;
    protected $path;

    public function __construct($url = '')
    {
        $this->url = urldecode($url);
        $this->path = $this->getPath();
    }

    protected function getPath()
    {
        $path = parse_url($this->url);
        $path = explode('&', $path['query']);
        $_path = [];
        foreach ($path as $p) {
            $_part = explode('=', $p);
            $_path[$_part[0]] = $_part[1];
        }
        return $_path;
    }

    public function getData()
    {
        $conf = new GenericConfiguration();
        $client = new \GuzzleHttp\Client();
        $request = new \ApaiIO\Request\GuzzleRequest($client);

        $conf
            ->setCountry('com')
            ->setAccessKey(env('AWS_PRODUCT_PUBLIC_KEY'))
            ->setSecretKey(env('AWS_PRODUCT_PRIVATE_KEY'))
            ->setAssociateTag(env('AMAZON_ASSOCIATE_TAG'))
            ->setRequest($request);
        $apaiIO = new ApaiIO($conf);
        \Log::emergency($this->path);

        $search = new Search();
        $search->setCategory('All');
        $keywords = [];
        if (isset($this->path['field-keywords'])) {
            $keywords[] = $this->path['field-keywords'];
        }
        if (isset($this->path['field-lbr_brands_browse-bin'])) {
            $keywords[] = $this->path['field-lbr_brands_browse-bin'];
        }
        if (isset($this->path['field-brandtextbin'])) {
            $keywords[] = $this->path['field-brandtextbin'];
        }
        if (isset($this->path['keywords'])) {
            $keywords[] = $this->path['keywords'];
        }
        $search->setResponseGroup(['Large', 'Images', 'ItemAttributes', 'Offers']);
        \Log::alert($keywords);
        if (!empty($keywords)) {
            $search->setKeywords(implode(',', $keywords));
        }

        $result = $apaiIO->runOperation($search);
        $_res = simplexml_load_string($result);
        $result = json_encode($_res->Items);
        $_res = json_decode($result, true);

        $_data = [];
        if (!empty($_res)) {
            if (isset($_res['Item'])) {
                foreach ($_res['Item'] as $key => $item) {
                    #if($item)
                    #    dump($item);
                    $item = collect($item);

                    if ($imageSet = $item->get('ImageSet')) {
                        $imageSet = collect($imageSet);
                        if ($mediumImage = $imageSet->get('MediumImage')) {
                            $_data['image'] = $mediumImage['URL'];
                        } elseif ($tinyImage = $imageSet->get('TinyImage')) {
                            $_data['image'] = $tinyImage['URL'];
                        }
                    }

                    if ($attributes = $item->get('LowestNewPrice')) {
                        $_data['price'] = $attributes['FormattedPrice'];
                        $_data['button_text'] = 'Buy now for ' . $_data['price'];
                    }

                    if ($title = $item->get('Title')) {
                        $_data['title'] = $title;
                    }

                    if ($links = $item->get('ItemLink')) {
                        collect($links)->each(function($index, $key) use (&$_data) {
                            if (strtolower($index['Note']) == 'technical details') {
                                $_data['url'] = $index['URL'];
                            }
                        });
                    }


                    if (isset($item['ItemAttributes']['ListPrice'])) {
                        $price = isset($item['ItemAttributes']['ListPrice']) && isset($item['ItemAttributes']['ListPrice']['FormattedPrice']) ? $item['ItemAttributes']['ListPrice']['FormattedPrice'] : 0;
                        $_data['title'] = $item['ItemAttributes']['Title'];
                        $_data['url'] = $item['DetailPageURL'];
                        $_data['price'] = $price;
                        $_data['button_text'] = 'Buy now for ' . $price;
                        $_data['image'] = $item['LargeImage']['URL'];
                        $_data['other'] = $item;
                    }
                }
            }
        }
        return $_data;
    }
}

function getMedium($array, $field, $value)
{
    foreach($array as $key => $item)
    {
        if ($item[$field] === $value)
            return $key;
    }
    return false;
}