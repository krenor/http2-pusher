<?php

use Illuminate\Http\Request;
use Krenor\Http2Pusher\Builder;
use Illuminate\Support\Collection;
use Krenor\Http2Pusher\Tests\TestCase;

class BuilderTest extends TestCase
{
    /** @test */
    public function it_should_retrieve_or_create_a_file_hash()
    {
        $reflector = new ReflectionClass(Builder::class);
        $method = $reflector->getMethod('hash');
        $method->setAccessible(true);

        $resources = new Collection([
            $this->pushable[0],
            "{$this->pushable[1]}?id=516707d9f36d4fb7d866",
            'https://laravel.com/assets/img/laravel-logo-white.png',
        ]);

        $resources->each(function ($resource, $index) use ($resources, $method) {
            $hash = $method->invokeArgs($this->builder, [$resource]);

            $this->assertNotNull($hash);

            if ($index === 1) {
                $mixFile = $resources[$index];
                $mixHash = substr($mixFile, -20, 12);

                $this->assertSame($mixHash, $hash);
            }
        });
    }

    /** @test */
    public function it_should_transform_resources_into_a_processable_structure()
    {
        $transformed = $this->transform($this->pushable);

        $transformed->each(function ($item) {
            $this->assertArrayHasKey('type', $item);
            $this->assertFalse($item['type'] === null);
            $this->assertArrayHasKey('hash', $item);
            $this->assertFalse($item['hash'] === null);
        });

        $this->assertEquals('script', $transformed[0]['type']);
        $this->assertEquals('style', $transformed[1]['type']);
        $this->assertEquals('image', $transformed[2]['type']);
        $this->assertEquals('image', $transformed[3]['type']);
        $this->assertEquals('image', $transformed[4]['type']);
        $this->assertEquals('font', $transformed[5]['type']);
    }

    /** @test */
    public function it_should_return_null_when_no_pushable_resource_is_available()
    {
        $push = $this->builder->prepare($this->nonPushable);

        $this->assertFalse($push !== null);
    }

    /** @test */
    public function it_should_build_the_push_link_when_given_pushable_resources()
    {
        $push = $this->builder->prepare($this->pushable);

        $expected = "<{$this->pushable[0]}>; rel=preload; as=script,";
        $expected .= "<{$this->pushable[1]}>; rel=preload; as=style,";
        $expected .= "<{$this->pushable[2]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable[3]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable[4]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable[5]}>; rel=preload; as=font; crossorigin";

        $this->assertNotNull($push);
        $this->assertSame($expected, $push->getLink());
    }

    /** @test */
    public function it_should_remove_non_pushable_resources_when_building_the_push_link()
    {
        $pushable = $this->pushable[0];

        $resources = array_merge(
            $this->nonPushable,
            [$pushable]
        );

        $push = $this->builder->prepare($resources);

        $this->assertFalse($push === null);

        foreach ($this->nonPushable as $item) {
            $this->assertNotContains($item, $push->getLink());
        }

        $this->assertContains($pushable, $push->getLink());
    }

    /** @test */
    public function it_should_add_a_cache_cookie_if_said_cookie_is_not_already_set()
    {
        $transformed = $this->transform($this->pushable);

        $push = $this->builder->prepare($this->pushable);

        $this->assertNotNull($push);
        $this->assertNotNull($push->getCookie());
        $this->assertNotNull($push->getResources());

        $this->assertSame($push->getCookie()->getValue(), $transformed->toJson());
        $this->assertSame($push->getResources()->pluck('path')->toArray(), $this->pushable);
    }

    /** @test */
    public function it_should_return_null_when_the_given_resources_already_got_cached()
    {
        $cache = $this->transform($this->pushable)
                      ->toJson();

        $cookies = [
            $this->builderSettings['cookie']['name'] => $cache,
        ];

        $request = new Request([], [], [], $cookies);

        $builder = new Builder($request, $this->builderSettings);

        $push = $builder->prepare($this->pushable);

        $this->assertNull($push);
    }

    /** @test */
    public function it_should_only_push_resources_which_are_not_cached_yet()
    {
        $pushed = array_slice($this->pushable, 0, 4);

        $cache = $this->transform($pushed)
                      ->toJson();

        $cookies = [
            $this->builderSettings['cookie']['name'] => $cache,
        ];

        $request = new Request([], [], [], $cookies);

        $builder = new Builder($request, $this->builderSettings);

        $push = $builder->prepare($this->pushable);

        $this->assertNotNull($push);
        $this->assertCount(
            count($this->pushable) - 4,
            explode(',', $push->getLink())
        );
        $this->assertSame(
            $this->transform($this->pushable)->toJson(),
            $push->getCookie()->getValue()
        );
    }

    /**
     * @param array $resources
     *
     * @return Collection
     */
    private function transform(array $resources): Collection
    {
        $reflector = new ReflectionClass(Builder::class);
        $method = $reflector->getMethod('transform');
        $method->setAccessible(true);

        return $method->invokeArgs($this->builder, [
            new Collection($resources),
        ]);
    }
}
