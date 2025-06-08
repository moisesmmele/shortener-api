<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware\Validation\Traits;

use Psr\Http\Message\ServerRequestInterface;

trait getShortcode
{
    //helper method to get shortcode from router url param or request
    private function getShortcode(ServerRequestInterface $request): string
    {
        return $request->getAttribute('shortcode') ?? '';
    }
}