<?php

namespace Krenor\Http2Pusher;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Cookie;

class Builder
{
    /**
     * The current request to read the cookie from.
     *
     * @var Request
     */
    protected $request;

    /**
     * Additional cookie and global pushable resources settings.
     *
     * @var array
     */
    protected $settings;

    /**
     * The supported extensions to push.
     *
     * @var array
     */
    protected $extensionTypes = [
        'css'   => 'style',
        'js'    => 'script',
        'ttf'   => 'font',
        'otf'   => 'font',
        'woff'  => 'font',
        'woff2' => 'font',
        'eot'   => 'font',
        'jpeg'  => 'image',
        'jpg'   => 'image',
        'png'   => 'image',
        'gif'   => 'image',
        'bmp'   => 'image',
        'svg'   => 'image',
    ];

    /**
     * Builder constructor.
     *
     * @param Request $request
     * @param array $settings
     */
    public function __construct(Request $request, array $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    /**
     * Build the HTTP2 Push link and cache digest cookie.
     *
     * @see https://w3c.github.io/preload/#server-push-(http/2)
     *
     * @param array $resources
     *
     * @return Http2Push|null
     */
    public function prepare(array $resources): ?Http2Push
    {
        $supported = collect($resources)
            ->merge($this->settings['global_pushes'])
            ->filter(function ($resource) {
                return array_key_exists($this->getExtension($resource), $this->extensionTypes);
            });

        if ($supported->count() < 1) {
            return null;
        }

        $transformed = $this->transform($supported);
        $cookie = $this->request->cookie($this->settings['cookie']['name']);

        $pushable = $this->processCookieCache($transformed, $cookie);

        if ($pushable->count() < 1) {
            return null;
        }

        $link = $this->buildLink($pushable);

        $cookie = new Cookie(
            $this->settings['cookie']['name'],
            $transformed->toJson(),
            strtotime("+{$this->settings['cookie']['duration']}")
        );

        return new Http2Push($pushable, $cookie, $link);
    }

    /**
     * Transform the resources to include a pushable type.
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    private function transform(Collection $collection): Collection
    {
        return $collection->map(function ($path) {
            $hash = $this->retrieveHash($path);

            $extension = $this->getExtension($path);

            $type = $this->extensionTypes[$extension];

            return compact('path', 'type', 'hash');
        });
    }

    /**
     * Generate or get a hash of a file.
     *
     * @param string $path
     *
     * @return string
     */
    private function retrieveHash(string $path): string
    {
        $pieces = parse_url($path);

        // External url
        if (isset($pieces['host'])) {
            return substr(hash_file('md5', $path), 0, 12);
        }

        // TODO: Might want to check for additional version strings other than Mixs'.
        preg_match('/id=([a-f0-9]{20})/', $path, $matches);

        if (last($matches)) {
            return substr(last($matches), 0, 12);
        }

        return substr(hash_file('md5', public_path($path)), 0, 12);
    }


    /**
     * Get the extension of a file and remove query parameters.
     *
     * @param string $path
     *
     * @return string
     */
    private function getExtension($path): string
    {
        return strtok(
            pathinfo($path, PATHINFO_EXTENSION),
            '?'
        );
    }

    /**
     * Check which resources already are cached.
     *
     * @param Collection $pushable
     * @param string|null $cache
     *
     * @return Collection
     */
    private function processCookieCache(Collection $pushable, $cache = null): Collection
    {
        if ($cache === null) {
            return $pushable;
        }

        if ($cache === $pushable->toJson()) {
            return collect();
        }

        $cached = json_decode($cache, true);

        return $pushable->filter(function ($item) use ($cached) {
            return !in_array($item, $cached);
        });
    }

    /**
     * Create the HTTP2 Server Push link.
     *
     * @param Collection $pushable
     *
     * @return string
     */
    private function buildLink(Collection $pushable): string
    {
        return $pushable->map(function ($item) {
            $push = "<{$item['path']}>; rel=preload; as={$item['type']}";

            if ($item['type'] === 'font') {
                return "{$push}; crossorigin";
            }

            return $push;
        })->implode(',');
    }
}
