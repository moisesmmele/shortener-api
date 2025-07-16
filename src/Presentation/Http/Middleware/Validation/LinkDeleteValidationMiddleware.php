<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware\Validation\Traits;

use Moises\ShortenerApi\Presentation\Http\Middleware\Validation\ValidationMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LinkDeleteValidationMiddleware implements ValidationMiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO: Implement process() method.
    }

    function validate(ServerRequestInterface $request): ?array
    {
        // TODO: Implement validate() method.
    }
}