<?php

use Krenor\Http2Pusher\Response;
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
    public function it_should_crawl_for_scripts_and_push_them()
    {
        $response = $this->middleware->handle(
            $this->request,
            $this->getResponse('with-scripts')
        );

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains($this->pushable['external'][0], $link);
        $this->assertContains($this->pushable['internal'][0], $link);
        $this->assertContains('/js/manifest.js', $link);
        $this->assertContains('/js/vendor.js', $link);
        $this->assertStringEndsWith('as=script', $link);

        $this->assertCount(4, explode(',', $link));
    }

    /** @test */
    public function it_should_crawl_for_style_sheets_and_push_them()
    {
        $response = $this->middleware->handle(
            $this->request,
            $this->getResponse('with-styles')
        );

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains($this->pushable['internal'][1], $link);
        $this->assertContains($this->pushable['external'][1], $link);
        $this->assertStringEndsWith('as=style', $link);

        $this->assertCount(2, explode(',', $link));
    }

    /** @test */
    public function it_should_crawl_for_images_and_push_them()
    {
        $response = $this->middleware->handle(
            $this->request,
            $this->getResponse('with-images')
        );

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains($this->pushable['internal'][2], $link);
        $this->assertContains($this->pushable['internal'][3], $link);
        $this->assertContains($this->pushable['internal'][4], $link);
        $this->assertContains($this->pushable['external'][2], $link);
        $this->assertContains($this->pushable['external'][3], $link);
        $this->assertStringEndsWith('as=image', $link);

        $this->assertCount(5, explode(',', $link));
    }

    /**
     * @param string $page
     *
     * @return Closure
     */
    private function getResponse($page)
    {
        return function ($request) use ($page) {
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
