<?php

use Illuminate\Http\Request;
use Krenor\Http2Pusher\Builder;
use Krenor\Http2Pusher\Tests\TestCase;

class BuilderTest extends TestCase
{
    /** @test */
    public function it_should_transform_internal_resources_into_a_proper_structure()
    {
        $transformed = $this->transform($this->pushable['internal']);

        $transformed->each(function ($item) {
            $this->assertArrayHasKey('type', $item);
            $this->assertFalse($item['type'] === null);
            $this->assertArrayHasKey('hash', $item);
            $this->assertFalse($item['hash'] === null);
        });

        $this->assertTrue($transformed[0]['type'] === 'script');
        $this->assertTrue($transformed[1]['type'] === 'style');
        $this->assertTrue($transformed[2]['type'] === 'image');
        $this->assertTrue($transformed[3]['type'] === 'image');
        $this->assertTrue($transformed[4]['type'] === 'image');
    }

    /** @test */
    public function it_should_transform_external_resources_into_a_proper_structure()
    {
        $transformed = $this->transform($this->pushable['external']);

        $transformed->each(function ($item) {
            $this->assertArrayHasKey('type', $item);
            $this->assertFalse($item['type'] === null);
            $this->assertArrayHasKey('hash', $item);
            $this->assertFalse($item['hash'] === null);
        });

        $this->assertTrue($transformed[0]['type'] === 'script');
        $this->assertTrue($transformed[1]['type'] === 'style');
        $this->assertTrue($transformed[2]['type'] === 'image');
        $this->assertTrue($transformed[3]['type'] === 'image');
    }

    /** @test */
    public function it_should_transform_without_recalculating_the_hash_of_mix_versioned_files()
    {
        $resources = [
            '/js/app.js?id=8e38929b2d5501e6808e',
            '/css/app.css?id=516707d9f36d4fb7d866',
        ];

        $transformed = $this->transform($resources);

        $transformed->each(function ($item, $index) use ($resources) {
            $this->assertArrayHasKey('type', $item);
            $this->assertFalse($item['type'] === null);

            $this->assertArrayHasKey('hash', $item);
            $this->assertFalse($item['hash'] !== substr($resources[$index], -20, 12));
        });

        $this->assertTrue($transformed[0]['type'] === 'script');
        $this->assertTrue($transformed[1]['type'] === 'style');
    }

    /** @test */
    public function it_should_return_null_when_no_pushable_resource_is_available()
    {
        $push = $this->builder->prepare($this->nonPushable);

        $this->assertFalse($push !== null);
    }


    /** @test */
    public function it_should_strip_non_pushable_resources_from_the_result()
    {
        $pushable = $this->pushable['internal'][0];

        $push = $this->builder->prepare(array_merge(
            $this->nonPushable,
            [$pushable]
        ));

        $this->assertFalse($push === null);

        foreach ($this->nonPushable as $item) {
            $this->assertNotContains($item, $push->getLink());
        }

        $this->assertContains($pushable, $push->getLink());
    }

    /** @test */
    public function it_should_build_the_push_string_correctly_for_internal_assets()
    {
        $push = $this->builder->prepare($this->pushable['internal']);

        $expected = "<{$this->pushable['internal'][0]}>; rel=preload; as=script,";
        $expected .= "<{$this->pushable['internal'][1]}>; rel=preload; as=style,";
        $expected .= "<{$this->pushable['internal'][2]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable['internal'][3]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable['internal'][4]}>; rel=preload; as=image";

        $this->assertSame($expected, $push->getLink());
    }

    /** @test */
    public function it_should_build_the_push_string_correctly_for_external_assets()
    {
        $push = $this->builder->prepare($this->pushable['external']);

        $expected = "<{$this->pushable['external'][0]}>; rel=preload; as=script,";
        $expected .= "<{$this->pushable['external'][1]}>; rel=preload; as=style,";
        $expected .= "<{$this->pushable['external'][2]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable['external'][3]}>; rel=preload; as=image";

        $this->assertSame($expected, $push->getLink());
    }

    /** @test */
    public function it_should_add_a_cache_digest_cookie_if_not_already_set()
    {
        $push = $this->builder->prepare($this->pushable['internal']);

        $transformed = $this->transform($this->pushable['internal']);

        $this->assertNotNull($push->getCookie());
        $this->assertNotNull($push->getLink());
        $this->assertSame($push->getCookie()->getValue(), $transformed->toJson());
    }

    /** @test */
    public function it_should_not_push_cached_resources_again()
    {
        $cache = $this->transform($this->pushable['internal'])
                      ->toJson();

        $cookies = [
            $this->builderSettings['name'] => $cache,
        ];

        $request = new Request([], [], [], $cookies);

        $builder = new Builder($request, $this->builderSettings);

        $push = $builder->prepare($this->pushable['internal']);

        $this->assertNull($push);
    }

    /** @test */
    public function it_should_only_push_new_not_already_cached_resources()
    {
        $pushed = array_slice($this->pushable['internal'], 0, 4);

        $cache = $this->transform($pushed)
                      ->toJson();

        $cookies = [
            $this->builderSettings['name'] => $cache,
        ];

        $request = new Request([], [], [], $cookies);

        $builder = new Builder($request, $this->builderSettings);

        $push = $builder->prepare($this->pushable['internal']);

        $this->assertNotNull($push);
        $this->assertCount(1, explode(',', $push->getLink()));

        $this->assertSame(
            $this->transform($this->pushable['internal'])->toArray(),
            json_decode($push->getCookie()->getValue(), true)
        );
    }

    /**
     * @param array $resources
     *
     * @return \Illuminate\Support\Collection
     */
    private function transform(array $resources)
    {
        $reflector = new ReflectionClass(Builder::class);
        $method = $reflector->getMethod('transform');
        $method->setAccessible(true);

        return $method->invokeArgs($this->builder, [
            collect($resources),
        ]);
    }
}
