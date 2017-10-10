<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cookie
    |--------------------------------------------------------------------------
    |
    | This setting allows you to set a name for the cookie which will be used
    | for caching already pushed resources. You can also determine the
    | duration of said cookie with a valid 'strtotime' value.
    |
    */

    'cookie' => [
        'name'     => 'h2_cache-digest',
        'duration' => '60 days',
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | This setting allows you to enable or disable crawling the DOM for any
    | images, scripts or stylesheets to include. Also you can decide whether
    | or not content of the manifest file should be pushed, too.
    |
    */

    'middleware' => [
        'crawl_dom' => true,
        'manifest'  => [
            'include' => true,
            'path'    => public_path('mix-manifest.json'),
        ],
    ],
];
