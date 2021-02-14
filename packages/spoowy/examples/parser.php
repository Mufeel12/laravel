<?php

require_once "../../../vendor/autoload.php";

use Spoowy\Amazon\AmazonUrl;


$seller = AmazonUrl::read('http://www.amazon.com/gp/aag/main/ref=olp_merch_name_2?ie=UTF8&asin=0852295316&isAmazonFulfilled=0&seller=A1KU5VMU83Q22A');
//$seller2 = AmazonUrl::read('http://www.amazon.com/gp/aag/main?ie=UTF8&asin=&isAmazonFulfilled=&isCBA=&marketplaceID=ATVPDKIKX0DER&orderID=&seller=A3KO3PI5YS5IC9&sshmPath=a');
//$product = AmazonUrl::read('http://www.amazon.com/Little-Makes-Music-Monika-Bang-Campbell/dp/0152053050/ref=aag_m_pw_dp?ie=UTF8&m=A1QJ4UH6FW3UH1');
//$product = AmazonUrl::read('http://www.amazon.com/dp/B00VUKLZ4O'); // 404
//$product = AmazonUrl::read('http://www.amazon.com/Wound-Care-Collaborative-Practice-Professionals/dp/1608317153/ref=aag_m_pw_dp?ie=UTF8&m=A1KU5VMU83Q22A');
//$product = AmazonUrl::read('http://www.amazon.com/dp/0804126119');
var_dump($seller->toArray());
//var_dump($seller2->toJson());
//var_dump($product->toJson());

