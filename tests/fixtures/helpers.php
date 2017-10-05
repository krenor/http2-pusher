<?php

use Illuminate\Container\Container;

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string $path
     *
     * @return string
     */
    function public_path($path = '')
    {
        $destination = ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);

        return Container::getInstance()
                        ->make('path.public') . $destination;
    }
}
