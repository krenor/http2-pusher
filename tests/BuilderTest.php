<?php

use Krenor\Http2Pusher\Builder;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var array
     */
    private $pushable = [
        '/js/app.js',
        '/css/app.css',
        '/images/logo.svg',
        '/images/image.png',
    ];

    /**
     * @var array
     */
    private $nonPushable = [
        '/app.less',
        '/app.coffee',
        '/uploads/passwords.txt',
        '/uploads/tax-return.pdf',
    ];

    /** @test */
    public function it_should_transform_resources_into_a_proper_structure()
    {
        $paths = array_merge($this->pushable, $this->nonPushable);

        $resources = collect($paths)->map(function ($path) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            $dictionary = [
                'css' => 'style',
                'js'  => 'script',
            ];

            $type = $dictionary[$extension] ?? 'image';

            return compact('path', 'type');
        });

        $resources->each(function ($item) {
            $this->assertArrayHasKey('type', $item);
            $this->assertFalse($item['type'] === null);
        });

        $this->assertTrue($resources[0]['type'] === 'script');
        $this->assertTrue($resources[1]['type'] === 'style');

        $resources->slice(2)->each(function ($item) {
            $this->assertTrue($item['type'] === 'image');
        });
    }

    /** @test */
    public function it_should_return_null_when_no_pushable_resource_is_available()
    {
        $pusher = new Builder($this->nonPushable);

        $result = $pusher->prepare();

        $this->assertFalse($result !== null);
    }

    /** @test */
    public function it_should_build_the_push_string_correctly()
    {
        $pusher = new Builder($this->pushable);

        $result = $pusher->prepare();

        $this->assertContains("<{$this->pushable[0]}>; rel=preload; as=script", $result);
        $this->assertContains("<{$this->pushable[1]}>; rel=preload; as=style", $result);
        $this->assertContains("<{$this->pushable[2]}>; rel=preload; as=image", $result);
        $this->assertContains("<{$this->pushable[3]}>; rel=preload; as=image", $result);

        $expected = "<{$this->pushable[0]}>; rel=preload; as=script,";
        $expected .= "<{$this->pushable[1]}>; rel=preload; as=style,";
        $expected .= "<{$this->pushable[2]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable[3]}>; rel=preload; as=image";

        $this->assertEquals($expected, $result);
    }
}
