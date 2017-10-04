<?php

namespace Krenor\Http2Pusher;

use Illuminate\Http\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @param array $resources
     *
     * @return $this
     */
    public function pushes(array $resources)
    {
        $header = (new Builder($resources))->prepare();

        if ($header !== null) {
            $this->header('Link', $header);
        }

        return $this;
    }
}
