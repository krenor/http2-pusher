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
    | Global Pushes
    |--------------------------------------------------------------------------
    |
    | Place URLs of any stylesheets, scripts, images or fonts you want to be
    | pushed for every page load.
    |
    */

    'global_pushes' => [
        //
    ],
];
