<?php

namespace Krenor\Http2Pusher\Tests;

use Krenor\Http2Pusher\Response;

class ResponseTest extends TestCase
{
    /** @test */
    public function it_should_not_set_a_link_header_or_cookie_without_pushable_resources()
    {
        $response = new Response();

        $response->pushes($this->request, $this->nonPushable);

        $headers = $response->headers;

        $this->assertFalse($headers->has('Link'));
        $this->assertCount(0, $headers->getCookies());
    }

    /** @test */
    public function it_should_add_a_link_header_and_cookie_for_cache_digestion()
    {
        $response = new Response();

        $response->pushes($this->request, $this->pushable['internal']);

        $headers = $response->headers;

        $this->assertTrue($headers->has('Link'));
        $this->assertCount(1, $headers->getCookies());
        $this->assertSame('h2_cache-digest', $headers->getCookies()[0]->getName());
    }
}
