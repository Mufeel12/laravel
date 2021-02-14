<?php

require_once '../../../vendor/autoload.php';

$config = require_once __DIR__ . '/../src/StockImages/Laravel/config/stockimages.php';


$imageBay = new \Spoowy\StockImages\StockImages($config);
//$result = $imageBay->search('apple', ['flickr', 'wikimedia']); // search just at flickr and wikimedia
$result = $imageBay->search('apple');

print($result->toJson());