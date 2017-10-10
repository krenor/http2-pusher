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
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->isRedirection() || $request->isJson() || $request->ajax()) {
            return $response;
        }

        $response = $response->pushes(
            $this->builder,
            $this->retrieveLinkableElements($response)
        );

        return $response;
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
        $crawler = new Crawler($response->content());

        $content = $crawler->filter('link, script[src], img[src]')
                           ->extract(['src', 'href']);

        // TODO: Ordering might be necessary for manifest files due to https://laravel.com/docs/5.5/mix#vendor-extraction
        return collect($content)->flatten(1)
                                ->filter()
                                ->toArray();
    }
}
