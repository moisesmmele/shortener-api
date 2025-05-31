<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Router;

use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use League\Route\Http\Exception\NotFoundException;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Strategy\ApplicationStrategy;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use League\Route\Router;
use DI\Container;

class LeagueRouterAdapter implements RouterInterface
{
    private Router $router;
    private LoggerInterface $logger;

    public function __construct(Router $leagueRouter, ContainerInterface $container, LoggerInterface $logger)
    {
        $this->router = $leagueRouter;
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($container);
        $this->router->setStrategy($strategy);
        $this->loadRoutes();
        $this->logger = $logger;
    }

    public function dispatch(): ResponseInterface
    {
        $request = ServerRequestFactory::fromGlobals();
        $method = $request->getMethod();
        $uri = $request->getUri();
        $path = $uri->getPath();
        $logContext = [
            'class' => get_class($this),
            'method' => __METHOD__,
            'request' => [
                'method' => $method,
                'path' => $path,
            ]
        ];
        try {
            $this->logger->info("New Request [$method] [$path]", $logContext);
            $response = $this->router->dispatch($request);
        } catch (NotFoundException $exception) {
            $this->logger->info("[$method] [$path] 404 Not Found", $logContext);
            $response = new JsonResponse(['statusCode' => '404', 'message' => '404 Not Found'], 404);
        }
        return $response;
    }

    public function handleResponse(ResponseInterface $response): void
    {
        (new SapiEmitter())->emit($response);
    }
    public function loadRoutes(): void
    {
        try {
            (require BASE_PATH . "/config/routes.php")($this);
        } catch (\Exception $exception) {
            $logContext = [
                'class' => get_class($this),
                'method' => __METHOD__,
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ];
            $this->logger->critical('could not load routes.', $logContext);
        }
    }

    public function get(string $uri, callable|array|string $handler): void
    {
        $this->router->map('GET', $uri, $handler);
    }

    public function post(string $uri, callable|array|string $handler): void
    {
        $this->router->map('POST', $uri, $handler);
    }

    public function put(string $uri, callable|array|string $handler): void
    {
        $this->router->map('PUT', $uri, $handler);
    }

    public function patch(string $uri, callable|array|string $handler): void
    {
        $this->router->map('PATCH', $uri, $handler);
    }

    public function delete(string $uri, callable|array|string $handler): void
    {
        $this->router->map('DELETE', $uri, $handler);
    }

    public function options(string $uri, callable|array|string $handler): void
    {
        $this->router->map('OPTIONS', $uri, $handler);
    }
}
