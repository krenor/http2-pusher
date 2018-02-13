<?php

namespace Krenor\Http2Pusher\Tests;

use Illuminate\Http\Request;
use Krenor\Http2Pusher\Builder;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $builderSettings = [
        'cookie'        => [
            'name'     => 'h2_cache-digest',
            'duration' => '60 days',
        ],
        'global_pushes' => [
            //
        ],
    ];

    /**
     * @var array
     */
    protected $pushable = [
        '/js/app.js',
        '/css/app.css',
        '/images/chrome.svg',
        '/images/github.png',
        '/images/laravel.jpg',
        '/fonts/lato-light.woff2',
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
     * Bootstrap the test environment.
     */
    public function setUp()
    {
        // The function "public_path" is in "Illuminate/Foundation" which is no standalone dependency.
        Container::getInstance()
                 ->instance('path.public', __DIR__ . DIRECTORY_SEPARATOR . 'fixtures');

        $this->request = new Request();
        $this->builder = new Builder($this->request, $this->builderSettings);
    }
}
