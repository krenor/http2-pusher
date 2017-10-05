<?php

namespace Krenor\Http2Pusher\Tests;

use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var array
     */
    protected $pushable = [
        'internal' => [
            '/js/app.js',
            '/css/app.css',
            '/images/chrome.svg',
            '/images/github.png',
            '/images/laravel.jpg',
        ],
        'external' => [
            'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta/js/bootstrap.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta/css/bootstrap-grid.min.css',
            'https://laravel.com/assets/img/laravel-logo-white.png',
            'http://stylecampaign.com/blog/blogimages/SVG/fox-1.svg',
        ],
    ];

    /**
     * @var array
     */
    protected $nonPushable = [
        '/app.less',
        '/app.coffee',
        '/uploads/passwords.txt',
        '/uploads/tax-return.pdf',
    ];

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        // The function "public_path" is in "Illuminate/Foundation" which is no standalone dependency.
        Container::getInstance()
                 ->instance('path.public', __DIR__ . DIRECTORY_SEPARATOR . 'fixtures');
    }
}
