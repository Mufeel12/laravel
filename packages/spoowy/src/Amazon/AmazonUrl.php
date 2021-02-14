<?php namespace Spoowy\Amazon;

use App\Video;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Spoowy\Amazon\Parsers\AsinParser;
use Spoowy\Amazon\Parsers\ItemDetails;

class AmazonUrl
{
    /**
     * Parse amazon url
     *
     * @param $url
     * @return \Spoowy\Product|Collection
     */
    public static function read($url)
    {
        // Parse ASIN
        $asin = AsinParser::parse($url);

        if ($asin instanceof Collection) {

            $collection = new Collection();
            $collection['seller'] = $asin['seller'];
            $collection['url'] = $asin['url'];
            $collection['products'] = new Collection();

            for ($i = 0; $i < count($asin['numbers']); $i++) {
                $item = new ItemDetails($asin['numbers'][$i]);
                $product = $item->data();
                $image_url = $product->image_url;

                if (!empty($image_url)) {
                    $collection['products'][] = $item->data();
                }
            }

            return $collection;
        }

        $item = new ItemDetails($asin);
        $product = $item->data();

        return $product;
    }

    /**
     * Looks up asin and returns code
     *
     * @param $asin
     * @return Collection
     */
    public static function lookup($asin)
    {
        $params = [
            'Operation' => 'ItemLookup',
            'ItemId' => $asin,
            'ResponseGroup' => 'ItemAttributes'
        ];
        $url = self::aws_signed_request($params);

        $client = new Client();
        $response = $client->get($url);
        return collect(simplexml_load_string($response->getBody()));
    }

    public static function aws_signed_request($params, $region = false, $public_key = false, $private_key = false, $associate_tag = NULL, $version = '2011-08-01')
    {
        /*
        Copyright (c) 2009-2012 Ulrich Mierendorff

        Permission is hereby granted, free of charge, to any person obtaining a
        copy of this software and associated documentation files (the "Software"),
        to deal in the Software without restriction, including without limitation
        the rights to use, copy, modify, merge, publish, distribute, sublicense,
        and/or sell copies of the Software, and to permit persons to whom the
        Software is furnished to do so, subject to the following conditions:

        The above copyright notice and this permission notice shall be included in
        all copies or substantial portions of the Software.

        THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
        IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
        FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
        THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
        LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
        FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
        DEALINGS IN THE SOFTWARE.
        */

        /*
        Parameters:
            $region - the Amazon(r) region (ca,com,co.uk,de,fr,co.jp)
            $params - an array of parameters, eg. array("Operation"=>"ItemLookup",
                            "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
            $public_key - your "Access Key ID"
            $private_key - your "Secret Access Key"
            $version (optional)
        */

        // Set default env variables
        $region = ($region == false ? 'com' : $region);
        $public_key = ($public_key == false ? env('AWS_PRODUCT_PUBLIC_KEY') : $public_key);
        $private_key = ($private_key == false ? env('AWS_PRODUCT_PRIVATE_KEY') : $private_key);
        $associate_tag = ($associate_tag == null ? env('AMAZON_ASSOCIATE_TAG') : $associate_tag);

        // some paramters
        $method = 'GET';
        $host = 'webservices.amazon.' . $region;
        $uri = '/onca/xml';

        // additional parameters
        $params['Service'] = 'AWSECommerceService';
        $params['AWSAccessKeyId'] = $public_key;
        // GMT timestamp
        $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
        // API version
        $params['Version'] = $version;
        if ($associate_tag !== NULL) {
            $params['AssociateTag'] = $associate_tag;
        }

        // sort the parameters
        ksort($params);

        // create the canonicalized query
        $canonicalized_query = array();
        foreach ($params as $param => $value) {
            $param = str_replace('%7E', '~', rawurlencode($param));
            $value = str_replace('%7E', '~', rawurlencode($value));
            $canonicalized_query[] = $param . '=' . $value;
        }
        $canonicalized_query = implode('&', $canonicalized_query);

        // create the string to sign
        $string_to_sign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;

        // calculate HMAC with SHA256 and base64-encoding
        $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $private_key, TRUE));

        // encode the signature for the request
        $signature = str_replace('%7E', '~', rawurlencode($signature));

        // create request
        $request = 'http://' . $host . $uri . '?' . $canonicalized_query . '&Signature=' . $signature;

        return $request;
    }

    /**
     * Take url and return signed url
     *
     * @param $url
     * @return string
     */
    public static function deliverS3Url($url, Video $video)
    {
        // Get file name from url
        $filename = basename($url);

        $cloudFront = new \Aws\CloudFront\CloudFrontClient([
            'region' => 'us-east-1',
            'version' => '2017-03-25'
        ]);

        // Setup parameter values for the resource
        $streamHostUrl = 'https://d150v4yoc0klrv.cloudfront.net';
        $resourceKey = $filename;

        $expires = time() + 100000;
        if (isset($video->duration) && !empty($video->duration) && $video->duration == 0) {
            $expires = round(time() + ($video->duration + ($video->duration * 0.5))) + 100000;
        }


        // Create a signed URL for the resource using the canned policy
        $signedUrlCannedPolicy = $cloudFront->getSignedUrl([
            'url'         => $streamHostUrl . '/' . $resourceKey,
            'expires'     => $expires,
            'private_key' => base_path('config/pk-APKAJCERRHM47DCS4XUQ.pem'),
            'key_pair_id' => 'APKAJCERRHM47DCS4XUQ'
        ]);

        return $signedUrlCannedPolicy;
    }
}