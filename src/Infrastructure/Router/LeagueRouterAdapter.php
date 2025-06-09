<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Router;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LeagueRouterAdapter implements RouterInterface
{
    private Router $router;
    private LoggerInterface $logger;
    private ContainerInterface $container;

    public function __construct(Router $leagueRouter, ContainerInterface $container, LoggerInterface $logger)
    {
        $this->router = $leagueRouter;

        $this->container = $container;
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($container);
        $this->router->setStrategy($strategy);
        $this->loadRoutes();
        $this->logger = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $uri = $request->getUri();
        $path = $uri->getPath();
        $logContext = [
            'method' => __METHOD__,
            'request' => [
                'method' => $method,
                'path' => $path,
            ]
        ];
        try {
            $response = $this->router->dispatch($request);
        } catch (NotFoundException $exception) {
            $response = new JsonResponse(['statusCode' => '404', 'message' => '404 Not Found'], 404);
        }
        return $response;
    }

    public function handleResponse(ResponseInterface $response): ResponseInterface
    {
        (new SapiEmitter())->emit($response);
        return $response;
    }
    public function loadRoutes(): void
    {
        try {
            (require BASE_PATH . "/config/routes.php")($this);
        } catch (\Exception $exception) {
            $logContext = [
                'method' => __METHOD__,
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ];
            $this->logger->critical('could not load routes.', $logContext);
        }
    }

    public function get(string $uri, callable|array|string $handler, array $middleware = []): void
    {
        $route = $this->router->map('GET', $uri, $handler);
        $this->applyMiddleware($route, $middleware);
    }

    public function post(string $uri, callable|array|string $handler, array $middleware = []): void
    {
        $route = $this->router->map('POST', $uri, $handler);
        $this->applyMiddleware($route, $middleware);
    }

    public function put(string $uri, callable|array|string $handler, array $middleware = []): void
    {
        $route = $this->router->map('PUT', $uri, $handler);
        $this->applyMiddleware($route, $middleware);
    }

    public function patch(string $uri, callable|array|string $handler, array $middleware = []): void
    {
        $route = $this->router->map('PATCH', $uri, $handler);
        $this->applyMiddleware($route, $middleware);
    }

    public function delete(string $uri, callable|array|string $handler, array $middleware = []): void
    {
        $route = $this->router->map('DELETE', $uri, $handler);
        $this->applyMiddleware($route, $middleware);
    }

    public function options(string $uri, callable|array|string $handler, array $middleware = []): void
    {
        $route = $this->router->map('OPTIONS', $uri, $handler);
        $this->applyMiddleware($route, $middleware);
    }

    public function applyMiddleware($route, array $middleware): void
    {
        if (!empty($middleware)) {
            foreach ($middleware as $mw) {
                // If it's a string, resolve it from the container
                if (is_string($mw)) {
                    $middlewareInstance = $this->container->get($mw);
                    $route->middleware($middlewareInstance);
                } else {
                    // If it's already an instance, use it directly
                    $route->middleware($mw);
                }
            }
        }
    }

    public function route(ServerRequestInterface $request): void
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $this->logger->info("Request: [$method] [$path]");
        $response = $this->handle($request);

        $statusCode = $response->getStatusCode();
        $this->logger->info("Response: [$path] [$statusCode]");
        $this->handleResponse($response);
    }

    public function applyGlobalMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $mw) {
            if (is_string($mw)) {
                $middlewareInstance = $this->container->get($mw);
                $this->router->middleware($middlewareInstance);
            }
        }
    }
}
