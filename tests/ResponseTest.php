<?php

use Krenor\Http2Pusher\Response;
use Krenor\Http2Pusher\Tests\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @var Response
     */
    private $response;

    /**
     * Bootstrap the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->response = new Response();
    }

    /** @test */
    public function it_should_not_add_a_link_header_or_cookie_without_pushable_resources()
    {
        $this->response->pushes($this->builder, $this->nonPushable);

        $headers = $this->response->headers;

        $this->assertFalse($headers->has('Link'));
        $this->assertCount(0, $headers->getCookies());
    }

    /** @test */
    public function it_should_add_a_link_header_and_cookie_when_given_pushable_resources()
    {
        $this->response->pushes($this->builder, $this->pushable);

        $headers = $this->response->headers;

        $this->assertTrue($headers->has('Link'));
        $this->assertCount(1, $headers->getCookies());
        $this->assertSame(
            $this->builderSettings['cookie']['name'],
            $headers->getCookies()[0]->getName()
        );
    }
}
