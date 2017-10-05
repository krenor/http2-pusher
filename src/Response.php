<?php

namespace Krenor\Http2Pusher;

use Illuminate\Http\Request;
use Illuminate\Http\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @param Request $request
     * @param array $resources
     *
     * @return $this
     */
    public function pushes(Request $request, array $resources)
    {
        $push = (new Builder($request))->prepare($resources);

        if ($push !== null) {
            $this->header('Link', $push->getLink())
                 ->withCookie($push->getCookie());
        }

        return $this;
    }
}
