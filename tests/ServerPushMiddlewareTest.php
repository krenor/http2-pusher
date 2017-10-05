<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Krenor\Http2Pusher\Tests\TestCase;
use Krenor\Http2Pusher\Middleware\ServerPush;

class ServerPushMiddlewareTest extends TestCase
{
    /**
     * @var Request
     */
    private $request;

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

        $this->request = new Request();
        $this->middleware = new ServerPush();
    }

    /**
     * Clean the test environment for the next test.
     */
    public function tearDown()
    {
        $manifestFile = public_path('mix-manifest.json');

        if (file_exists($manifestFile)) {
            unlink($manifestFile);
        }
    }

    /** @test */
    public function it_should_not_set_a_link_header_without_pushable_resources()
    {
        $response = $this->middleware->handle($this->request, $this->getResponse('default'));

        $this->assertFalse($response->headers->has('Link'));
    }

    /** @test */
    public function it_should_push_the_content_of_the_mix_manifest_file()
    {
        $this->createManifestFile();

        $response = $this->middleware->handle($this->request, $this->getResponse('default'));

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains('style', $link);
        $this->assertContains('script', $link);
        $this->assertNotContains('image', $link);
        $this->assertCount(4, explode(',', $link));
    }

    /** @test */
    public function it_should_crawl_for_scripts_and_push_them()
    {
        $response = $this->middleware->handle($this->request, $this->getResponse('with-scripts'));

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains($this->pushable['internal'][0], $link);
        $this->assertContains($this->pushable['external'][0], $link);
        $this->assertStringEndsWith('as=script', $link);

        $this->assertCount(2, explode(',', $link));
    }

    /** @test */
    public function it_should_crawl_for_style_sheets_and_push_them()
    {
        $response = $this->middleware->handle($this->request, $this->getResponse('with-styles'));

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
        $response = $this->middleware->handle($this->request, $this->getResponse('with-images'));

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

    /** @test */
    public function it_should_not_add_the_same_resources_when_crawling_and_reading_the_mix_manifest_file()
    {
        $this->createManifestFile();
        
        $response = $this->middleware->handle($this->request, $this->getResponse('with-manifest-references'));

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains('style', $link);
        $this->assertContains('script', $link);
        $this->assertCount(6, explode(',', $link));
    }

    /**
     * @param string $fixture
     *
     * @return Closure
     */
    private function getResponse($fixture)
    {
        $content = file_get_contents(__DIR__ . "/fixtures/views/{$fixture}.blade.php");

        $response = new Response($content);

        return function ($request) use ($response) {
            return $response;
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
