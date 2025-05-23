<?php

namespace Moises\ShortenerApi\Application\Contracts\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    public function dispatch(): ResponseInterface;

    public function loadRoutes(): void;
    public function get(string $uri, array|callable|string $handler): void;
    public function post(string $uri, array|callable|string $handler): void;
    public function put(string $uri, array|callable|string $handler): void;
    public function patch(string $uri, array|callable|string $handler): void;
    public function delete(string $uri, array|callable|string $handler): void;
    public function options(string $uri, array|callable|string $handler): void;
}