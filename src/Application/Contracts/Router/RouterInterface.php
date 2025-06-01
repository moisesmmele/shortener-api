<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\Contracts\Router;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RouterInterface extends RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface;
    public function loadRoutes(): void;
    public function get(string $uri, array|callable|string $handler, array $middleware = []): void;
    public function post(string $uri, array|callable|string $handler, array $middleware = []): void;
    public function put(string $uri, array|callable|string $handler, array $middleware = []): void;
    public function patch(string $uri, array|callable|string $handler, array $middleware = []): void;
    public function delete(string $uri, array|callable|string $handler, array $middleware = []): void;
    public function options(string $uri, array|callable|string $handler, array $middleware = []): void;}
