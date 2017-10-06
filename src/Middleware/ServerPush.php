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
     * ServerPush constructor.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->isRedirection() || $request->isJson()) {
            return $response;
        }

        if (!$request->ajax()) {
            $resources = array_unique(array_merge(
                $this->retrieveManifestContents(),
                $this->retrieveLinkableElements($response)
            ));

            $response = $response->pushes($this->builder, $resources);
        }

        return $response;
    }

    /**
     * Read the mix manifest file for possible contents to push.
     *
     * @return array
     */
    private function retrieveManifestContents()
    {
        $manifestFile = public_path('mix-manifest.json');

        if (!file_exists($manifestFile)) {
            return [];
        }

        $content = json_decode(file_get_contents($manifestFile), true);

        // TODO: Ordering might be necessary here, too: https://laravel.com/docs/5.5/mix#vendor-extraction
        return array_values($content);
    }

    /**
     * @param Response $response
     *
     * @return array
     */
    private function retrieveLinkableElements(Response $response)
    {
        $crawler = new Crawler($response->getContent());

        $content = $crawler->filter('link, script[src], img[src]')
                           ->extract(['src', 'href']);

        return collect($content)->flatten(1)
                                ->filter()
                                ->toArray();
    }
}
