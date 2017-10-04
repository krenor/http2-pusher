<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use Krenor\Http2Pusher\Middleware\ServerPush;

class ServerPushTest extends TestCase
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
