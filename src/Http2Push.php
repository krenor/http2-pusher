<?php

namespace Krenor\Http2Pusher;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Cookie;

class Http2Push
{
    /**
     * @var Collection
     */
    protected $resources;

    /**
     * @var Cookie
     */
    protected $cookie;

    /**
     * @var string
     */
    protected $link;

    /**
     * PushableResource constructor.
     *
     * @param Collection $resources
     * @param Cookie $cookie
     * @param string $link
     */
    public function __construct(Collection $resources, Cookie $cookie, string $link)
    {
        $this->resources = $resources;
        $this->cookie = $cookie;
        $this->link = $link;
    }

    /**
     * @return Collection
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    /**
     * @return Cookie
     */
    public function getCookie(): Cookie
    {
        return $this->cookie;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }
}
