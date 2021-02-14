<?php

/**
 * Image hosting API parameters
 */
return [

    /**
     * Pixabay paramers
     */

    'pixabay' => [
        'url' => 'https://pixabay.com/api/',
        'username' => 'namesmile',
        'key' => 'b13ba73a82e250d6cf4a',
        'image_type' => 'all',
        'orientation' => 'all',
        'min_width' => 0,
        'min_height' => 0,
        'editors_choice' => FALSE,
        'safesearch' => FALSE,
        'order' => 'popular',
        'per_page' => 4,
    ],

//    'europeana' => [
//        'url' => 'http://europeana.eu/api/v2/search.json',
//        'api_key' => '3TegNxKWX',
//        'private_key' => '23A6qxf7a',
//        'per_page' => 4,
//    ],

    /**
     * Flickr parameters
     */

    'flickr' => [
        'url' => 'https://api.flickr.com/services/rest/',
        'api_key' => 'ccaf07953c845fc47599e3b35663dd53',
        'per_page' => 4,
    ],

    /**
     * Wikimedia parameters
     */
    'wikimedia' => [
        'url' => 'https://en.wikipedia.org/w/api.php',
        'per_page' => 4
    ],
];