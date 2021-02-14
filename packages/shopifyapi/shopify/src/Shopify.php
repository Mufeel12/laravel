<?php
namespace ShopifyApi\Shopify;


class Shopify {

    protected $url;
    protected $normalUrl;

    public function __construct($url = '')
    {
        $this->url = urldecode($url);
        $this->setUrl();
    }

    protected function setUrl()
    {
        $path = parse_url($this->url);
        $_path = explode('/', trim($path['path'], '/'));
        if (!empty($_path)) {
            $true_parts = [];
            $product_exist = false;
            foreach ($_path as $part) {
                $true_parts[] = $part;
                if ($part == 'products') {
                    $product_exist = true;
                    break;
                }
            }
            if (empty($true_parts)) {
                $endPath = implode('/', $_path);
            } else {
                $endPath = implode('/', $true_parts);
            }

            if (!$product_exist) {
                $endPath .= '/products';
            }
        }
        $this->normalUrl = $path['scheme'] . '://' . $path['host'] . '/' . $endPath;
        $this->url = $this->normalUrl . '.json?limit=10';
    }

    public function getData(){
        $cookie_file = base_path('cookies.txt');
        if (!file_exists($cookie_file)) {
            $c = fopen('cookies.txt', 'w+');
            fclose($c);
        }

        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->url,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ));
        // Send the reque st & save response to $resp
        $data = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);

        $_resp = \GuzzleHttp\json_decode($data, true);
        $_data = [];
        if (!empty($_resp) && !empty($_resp['products'])) {
            $key = 0;
            foreach ($_resp['products'] as $item) {
                $_data[$key]['title'] = $item['title'];
                $_data[$key]['url'] = $this->normalUrl . '/' . $item['handle'];
                $_data[$key]['price'] = $item['variants'][0]['price'] . '$';
                $_data[$key]['button_text'] = 'Buy now for ' . $item['variants'][0]['price'] . '$';
                $_data[$key]['image'] = $item['images'][0]['src'];
                $key++;
            }
        }
        return $_data;
    }
}