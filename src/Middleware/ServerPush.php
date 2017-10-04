<?php

namespace Krenor\Http2Pusher\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Krenor\Http2Pusher\Builder;
use Illuminate\Container\Container;
use Symfony\Component\DomCrawler\Crawler;

class ServerPush
{
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
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        if ($response->isRedirection() || $request->isJson()) {
            return $response;
        }

        if (!$request->ajax()) {
            $resources = $this->retrieveLinkableElements($response);

            $header = (new Builder($resources))->prepare();

            if ($header !== null) {
                $response->header('Link', $header);
            }

        }

        return $response;
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
