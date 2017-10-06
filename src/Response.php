<?php

namespace Krenor\Http2Pusher;

use Illuminate\Http\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @param Builder $builder
     * @param array $resources
     *
     * @return $this
     */
    public function pushes(Builder $builder, array $resources)
    {
        $push = $builder->prepare($resources);

        if ($push !== null) {
            $this->header('Link', $push->getLink())
                 ->withCookie($push->getCookie());
        }

        return $this;
    }
}
