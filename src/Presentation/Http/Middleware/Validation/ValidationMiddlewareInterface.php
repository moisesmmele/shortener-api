<?php

namespace Moises\ShortenerApi\Presentation\Http\Middleware\Validation;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface ValidationMiddlewareInterface extends MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param array $values
     * @return bool
     * @description Validate method should receive empty array as reference
     * to be populated with values after validation
     */
    function validate(ServerRequestInterface $request): ?array;
}