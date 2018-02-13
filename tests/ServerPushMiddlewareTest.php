<?php

use Krenor\Http2Pusher\Response;
use Illuminate\Http\RedirectResponse;
use Krenor\Http2Pusher\Tests\TestCase;
use Krenor\Http2Pusher\Middleware\ServerPush;

class ServerPushMiddlewareTest extends TestCase
{
    /**
     * @var ServerPush
     */
    private $middleware;

    /**
     * Bootstrap the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->createManifestFile();

        $this->middleware = new ServerPush($this->builder);
    }

    /**
     * Clean the test environment for the next test.
     */
    public function tearDown()
    {
        $manifest = public_path('mix-manifest.json');

        if (file_exists($manifest)) {
            unlink($manifest);
        }
    }

    /** @test */
    public function it_should_not_push_anything_when_its_a_redirect_response()
    {
        $next = function () {
            return new RedirectResponse('http://laravel.com');
        };

        $response = $this->middleware->handle(
            $this->request,
            $next
        );

        $this->assertFalse($response->headers->has('Link'));
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /** @test */
    public function it_should_not_push_anything_when_its_a_json_request()
    {
        $this->request->headers->set('Content-Type', 'application/json');

        $next = function () {
            return new Response;
        };

        $response = $this->middleware->handle(
            $this->request,
            $next
        );

        $this->assertFalse($response->headers->has('Link'));
    }

    /** @test */
    public function it_should_not_push_anything_when_its_an_ajax_request()
    {
        $this->request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $next = function () {
            return new Response;
        };

        $response = $this->middleware->handle(
            $this->request,
            $next
        );

        $this->assertFalse($response->headers->has('Link'));
    }

    /** @test */
    public function it_should_crawl_for_scripts_and_push_them()
    {
        $includes = [
            'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta/js/bootstrap.min.js',
            '/js/manifest.js',
            '/js/vendor.js',
            '/js/app.js',
        ];

        $response = $this->middleware->handle(
            $this->request,
            $this->getResponse('with-scripts')
        );

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        foreach ($includes as $include) {
            $this->assertContains($include, $link);
        }

        $this->assertCount(count($includes), explode(',', $link));
    }

    /** @test */
    public function it_should_crawl_for_style_sheets_and_push_them()
    {
        $includes = [
            'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta/css/bootstrap-grid.min.css',
            '/css/app.css',
        ];

        $response = $this->middleware->handle(
            $this->request,
            $this->getResponse('with-styles')
        );

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        foreach ($includes as $include) {
            $this->assertContains($include, $link);
        }

        $this->assertCount(count($includes), explode(',', $link));
    }

    /** @test */
    public function it_should_crawl_for_images_and_push_them()
    {
        $includes = [
            'http://stylecampaign.com/blog/blogimages/SVG/fox-1.svg',
            'https://laravel.com/assets/img/laravel-logo-white.png',
            '/images/laravel.jpg',
            '/images/chrome.svg',
            '/images/github.png',
        ];

        $response = $this->middleware->handle(
            $this->request,
            $this->getResponse('with-images')
        );

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        foreach ($includes as $include) {
            $this->assertContains($include, $link);
        }

        $this->assertCount(count($includes), explode(',', $link));
    }

    /**
     * @param string $page
     *
     * @return Closure
     */
    private function getResponse($page)
    {
        return function () use ($page) {
            return new Response(
                file_get_contents(__DIR__ . "/fixtures/pages/{$page}.html")
            );
        };
    }

    /**
     * Create the manifest file manually for tests regarding said file.
     *
     * @return void
     */
    private function createManifestFile()
    {
        $content = '{
            "/js/vendor.js": "/js/vendor.js?id=911084212bac1b5ea2a5",
            "/js/app.js": "/js/app.js?id=8e38929b2d5501e6808e",
            "/css/app.css": "/css/app.css?id=516707d9f36d4fb7d866",
            "/js/manifest.js": "/js/manifest.js?id=ac5def271276f7bf7ec1"
        }';

        file_put_contents(public_path('mix-manifest.json'), $content);
    }
}
