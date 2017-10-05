<?php

use Illuminate\Support\Str;
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
            $this->assertFalse($item['hash'] !== Str::substr($resources[$index], -20, 12));
        });

        $this->assertTrue($transformed[0]['type'] === 'script');
        $this->assertTrue($transformed[1]['type'] === 'style');
    }

    /** @test */
    public function it_should_return_null_when_no_pushable_resource_is_available()
    {
        $builder = new Builder($this->nonPushable);

        $prepared = $builder->prepare($this->request);

        $this->assertFalse($prepared !== null);
    }


    /** @test */
    public function it_should_strip_non_pushable_resources_from_the_result()
    {
        $pushable = $this->pushable['internal'][0];

        $builder = new Builder(array_merge(
            $this->nonPushable,
            [$pushable]
        ));

        $prepared = $builder->prepare($this->request);

        $this->assertFalse($prepared === null);

        foreach ($this->nonPushable as $item) {
            $this->assertNotContains($item, $prepared['link']);
        }

        $this->assertContains($pushable, $prepared['link']);
    }

    /** @test */
    public function it_should_build_the_push_string_correctly_for_internal_assets()
    {
        $builder = new Builder($this->pushable['internal']);

        $prepared = $builder->prepare($this->request);

        $expected = "<{$this->pushable['internal'][0]}>; rel=preload; as=script,";
        $expected .= "<{$this->pushable['internal'][1]}>; rel=preload; as=style,";
        $expected .= "<{$this->pushable['internal'][2]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable['internal'][3]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable['internal'][4]}>; rel=preload; as=image";

        $this->assertSame($expected, $prepared['link']);
    }

    /** @test */
    public function it_should_build_the_push_string_correctly_for_external_assets()
    {
        $builder = new Builder($this->pushable['external']);

        $prepared = $builder->prepare($this->request);

        $expected = "<{$this->pushable['external'][0]}>; rel=preload; as=script,";
        $expected .= "<{$this->pushable['external'][1]}>; rel=preload; as=style,";
        $expected .= "<{$this->pushable['external'][2]}>; rel=preload; as=image,";
        $expected .= "<{$this->pushable['external'][3]}>; rel=preload; as=image";

        $this->assertSame($expected, $prepared['link']);
    }

    /** @test */
    public function it_should_add_a_cache_digest_cookie_if_not_already_set()
    {
        $builder = new Builder($this->pushable['internal']);

        $prepared = $builder->prepare($this->request);

        $transformed = $this->transform($this->pushable['internal']);

        $this->assertNotNull($prepared['cookie']);
        $this->assertNotNull($prepared['link']);
        $this->assertSame($prepared['cookie']->getValue(), $transformed->toJson());
    }

    /** @test */
    public function it_should_not_push_cached_resources_again()
    {
        $cache = $this->transform($this->pushable['internal'])
                      ->toJson();

        $cookies = [
            'h2_cache-digest' => $cache,
        ];

        $request = new Request([], [], [], $cookies);

        $builder = new Builder($this->pushable['internal']);

        $prepared = $builder->prepare($request);

        $this->assertNull($prepared['link']);
        $this->assertNull($prepared['cookie']);
    }

    /** @test */
    public function it_should_only_push_new_not_already_cached_resources()
    {
        $pushed = array_slice($this->pushable['internal'], 0, 4);

        $cache = $this->transform($pushed)
                      ->toJson();

        $cookies = [
            'h2_cache-digest' => $cache,
        ];

        $request = new Request([], [], [], $cookies);

        $builder = new Builder($this->pushable['internal']);

        $prepared = $builder->prepare($request);

        $this->assertNotNull($prepared);
        $this->assertCount(1, explode(',', $prepared['link']));

        $this->assertSame(
            $this->transform($this->pushable['internal'])->toArray(),
            json_decode($prepared['cookie']->getValue(), true)
        );
    }

    /**
     * @param array $resources
     *
     * @return \Illuminate\Support\Collection
     */
    private function transform(array $resources)
    {
        $dictionary = [
            'css' => 'style',
            'js'  => 'script',
        ];

        return collect($resources)->map(function ($path) use ($dictionary) {
            $pieces = parse_url($path);

            if (isset($pieces['host'])) {
                $hash = substr(hash_file('md5', $path), 0, 12);
            } else {
                preg_match('/id=([a-f0-9]{20})/', $path, $matches);

                if (last($matches)) {
                    $hash = substr(last($matches), 0, 12);
                } else {
                    $hash = substr(hash_file('md5', public_path($path)), 0, 12);
                }
            }

            $extension = strtok(pathinfo($path, PATHINFO_EXTENSION), '?');

            $type = $dictionary[$extension] ?? 'image';

            return compact('path', 'type', 'hash');
        });
    }
}
