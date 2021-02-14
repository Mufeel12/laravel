<?php

require_once '../../../vendor/autoload.php';

$spotlight = new \Spoowy\SpotlightSearch\Spotlight();

$results = $spotlight->suggestion('Sa');


