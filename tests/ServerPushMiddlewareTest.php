<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
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
        $this->request = new Request();
        $this->middleware = new ServerPush();

        // "public_path" helper comes in "Illuminate/Foundation" which is no standalone dependency.
        Container::getInstance()->instance('path.public', false);
    }

    /** @test */
    public function it_should_read_contents_of_the_manifest_file()
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures';

        Container::getInstance()->instance('path.public', $path);

        $response = $this->middleware->handle($this->request, $this->getResponse('default'));

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains('style', $link);
        $this->assertContains('script', $link);
        $this->assertNotContains('image', $link);
        $this->assertCount(4, explode(',', $link));
    }

    /** @test */
    public function it_should_not_contain_same_resources_to_push()
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures';

        Container::getInstance()->instance('path.public', $path);

        $response = $this->middleware->handle($this->request, $this->getResponse('with-manifest-references'));

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains('style', $link);
        $this->assertContains('script', $link);
        $this->assertCount(6, explode(',', $link));
    }

    /** @test */
    public function it_should_not_set_a_link_header_without_pushable_resources()
    {
        $response = $this->middleware->handle($this->request, $this->getResponse('default'));

        $this->assertFalse($response->headers->has('Link'));
    }

    /** @test */
    public function it_should_auto_detect_images_to_add_to_the_link_header()
    {
        $response = $this->middleware->handle($this->request, $this->getResponse('with-images'));

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains('logo.svg', $link);
        $this->assertContains('mom.png', $link);
        $this->assertContains('my-dog.jpg', $link);
        $this->assertStringEndsWith('as=image', $link);

        $this->assertCount(3, explode(',', $link));
    }

    /** @test */
    public function it_should_auto_detect_scripts_to_add_files_to_the_link_header()
    {
        $response = $this->middleware->handle($this->request, $this->getResponse('with-scripts'));

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains('my-first-script.js', $link);
        $this->assertContains('https://cdnjs.cloudflare.com', $link);
        $this->assertStringEndsWith('as=script', $link);

        $this->assertCount(2, explode(',', $link));
    }

    /** @test */
    public function it_should_auto_detect_style_sheets_to_add_files_to_the_link_header()
    {
        $response = $this->middleware->handle($this->request, $this->getResponse('with-styles'));

        $this->assertTrue($response->headers->has('Link'));

        $link = $response->headers->get('Link');

        $this->assertContains('styling.css', $link);
        $this->assertContains('https://cdnjs.cloudflare.com', $link);
        $this->assertStringEndsWith('as=style', $link);

        $this->assertCount(2, explode(',', $link));
    }

    /**
     * @param string $fixture
     *
     * @return Closure
     */
    private function getResponse($fixture)
    {
        $content = file_get_contents(__DIR__ . "/fixtures/{$fixture}.blade.php");

        $response = new Response($content);

        return function ($request) use ($response) {
            return $response;
        };
    }
}
