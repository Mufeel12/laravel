<?php
namespace EbayApi\Ebay;

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;

class Ebay {

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
        foreach($path as $p){
            $_part = explode('=', $p);
            $_path[$_part[0]] = $_part[1];
        }
        return $_path;
    }

    public function getData(){
        $conf = new GenericConfiguration();
        $client = new \GuzzleHttp\Client();
        $request = new \ApaiIO\Request\GuzzleRequest($client);

        $conf
            ->setCountry('com')
            ->setAccessKey(env('AWS_ACCESS_KEY_ID'))
            ->setSecretKey(env('AWS_SECRET_ACCESS_KEY'))
            ->setAssociateTag(env('AMAZON_ASSOCIATE_TAG'))
            ->setRequest($request);
        $apaiIO = new ApaiIO($conf);

        $search = new Search();
        $search->setCategory('All');
        $keywords = [];
        if(isset($this->path['field-keywords'])) {
            $keywords[] = $this->path['field-keywords'];
        }
        if(isset($this->path['field-lbr_brands_browse-bin'])) {
            $keywords[] = $this->path['field-lbr_brands_browse-bin'];
        }
        if(isset($this->path['field-brandtextbin'])) {
            $keywords[] = $this->path['field-brandtextbin'];
        }
        $search->setResponseGroup(['Large', 'Images', 'ItemAttributes', 'Offers']);
        if (!empty($keywords)) {
            $search->setKeywords(implode(',', $keywords));
        }

        $result = $apaiIO->runOperation($search);

        $_res = simplexml_load_string($result);
        $result = json_encode($_res->Items);
        $_res = json_decode($result, true);

        $_data = [];
        if(!empty($_res)){

            foreach ($_res['Item'] as $key => $item){
                if(isset($item['MediumImage'])) {
                    $_data[$key]['title'] = $item['ItemAttributes']['Title'];
                    $_data[$key]['url'] = $item['DetailPageURL'];
                    $_data[$key]['price'] = $item['ItemAttributes']['ListPrice']['FormattedPrice'];
                    $_data[$key]['button_text'] = 'Buy now for ' . $item['ItemAttributes']['ListPrice']['FormattedPrice'];
                    $_data[$key]['image'] = $item['LargeImage']['URL'];
                }
            }
        }
        return $_data;
    }
}