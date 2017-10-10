<?php

namespace Krenor\Http2Pusher\Middleware;

use Closure;
use Illuminate\Http\Request;
use Krenor\Http2Pusher\Builder;
use Krenor\Http2Pusher\Response;
use Symfony\Component\DomCrawler\Crawler;

class ServerPush
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $settings;

    /**
     * ServerPush constructor.
     *
     * @param Builder $builder
     * @param array $settings
     */
    public function __construct(Builder $builder, array $settings)
    {
        $this->builder = $builder;
        $this->settings = $settings;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->isRedirection() || $request->isJson() || $request->ajax()) {
            return $response;
        }

        $resources = collect();

        if ($this->settings['manifest']['include']) {
            $resources = $resources->merge($this->retrieveManifestContents());
        }

        if ($this->settings['crawl_dom']) {
            $resources = $resources->merge($this->retrieveLinkableElements($response));
        }

        if ($resources->isNotEmpty()) {
            $pushable = $resources->unique()->toArray();
            $response = $response->pushes($this->builder, $pushable);
        }

        return $response;
    }

    /**
     * Read the manifest file for possible content to push.
     *
     * @return array
     */
    private function retrieveManifestContents(): array
    {
        $manifest = $this->settings['manifest']['path'];

        if (!file_exists($manifest)) {
            return [];
        }

        $content = file_get_contents($manifest);

        // TODO: Ordering might be necessary here, too: https://laravel.com/docs/5.5/mix#vendor-extraction
        return array_values(
            json_decode($content, true)
        );
    }

    /**
     * Crawl the DOM for possible content to push.
     *
     * @param Response $response
     *
     * @return array
     */
    private function retrieveLinkableElements(Response $response): array
    {
        $crawler = new Crawler($response->getContent());

        $content = $crawler->filter('link, script[src], img[src]')
                           ->extract(['src', 'href']);

        return collect($content)->flatten(1)
                                ->filter()
                                ->toArray();
    }
}
