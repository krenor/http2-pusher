<?php

namespace Krenor\Http2Pusher;

use Illuminate\Support\Collection;

class Builder
{
    /**
     * The resources to push via HTTP2.
     *
     * @var Collection
     */
    protected $resources;

    /**
     * The supported extensions to push.
     *
     * @var array
     */
    protected $supported = [
        'jpeg',
        'jpg',
        'png',
        'gif',
        'bmp',
        'svg',
        'css',
        'js',
    ];

    /**
     * Pusher constructor.
     *
     * @param array $resources
     */
    public function __construct(array $resources)
    {
        $this->resources = collect($resources);
    }

    /**
     * Build the HTTP2 Push link.
     *
     * @see https://w3c.github.io/preload/#server-push-(http/2)
     *
     * @return string|null
     */
    public function prepare()
    {
        $resources = $this->resources->filter(function ($resource) {
            return in_array($this->getExtension($resource), $this->supported);
        });

        if ($resources->count() < 1) {
            return null;
        }

        return $this->transform($resources)->map(function ($item) {
            return "<{$item['path']}>; rel=preload; as={$item['type']}";
        })->implode(',');
    }

    /**
     * Transform the resources to include a pushable type.
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    private function transform(Collection $collection)
    {
        $dictionary = [
            'css' => 'style',
            'js'  => 'script',
        ];

        return $collection->map(function ($path) use ($dictionary) {
            $hash = $this->determineHash($path);

            $extension = $this->getExtension($path);

            $type = $dictionary[$extension] ?? 'image';

            return compact('path', 'type', 'hash');
        });
    }

    /**
     * Get the extension of a file and remove query parameters.
     *
     * @param string $path
     *
     * @return string
     */
    private function getExtension($path)
    {
        return strtok(
            pathinfo($path, PATHINFO_EXTENSION),
            '?'
        );
    }

    /**
     * Generate or get a hash of a file.
     *
     * @param string $path
     *
     * @return string
     */
    private function determineHash(string $path): string
    {
        $pieces = parse_url($path);

        // External url
        if (isset($pieces['host'])) {
            return substr(hash_file('md5', $path), 0, 12);
        } else {
            // TODO: Might want to check for additional version strings other than Mixs'.
            preg_match('/id=([a-f0-9]{20})/', $path, $matches);

            if (last($matches)) {
                return substr(last($matches), 0, 12);
            }

            return substr(hash_file('md5', public_path($path)), 0, 12);
        }
    }
}
