<?php

namespace Moises\ShortenerApi\Infrastructure\Router;

use DI\Container;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;

class LeagueRouterAdapter implements RouterInterface
{
    private Router $router;
    public function __construct(Router $router, Container $container)
    {
        $this->router = $router;
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($container);
        $this->router->setStrategy($strategy);
        $this->loadRoutes();
    }

    public function dispatch(): ResponseInterface
    {
        $request = ServerRequestFactory::fromGlobals();
        $method = $request->getMethod();
        $uri = $request->getUri();

        try {
            $response = $this->router->dispatch($request);
            error_log("{$method} [$uri]..................... 200 OK");
        } catch (NotFoundException $exception) {
            error_log("{$method} @ [$uri]..................... 404 Not Found");
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
        (require BASE_PATH . "/config/routes.php")($this);
    }

    public function get(string $uri, callable|array|string $handler): void
    {
        $this->router->map('GET', $uri, $handler);
    }

    public function post(string $uri, callable|array|string $handler): void
    {
        // TODO: Implement post() method.
    }

    public function put(string $uri, callable|array|string $handler): void
    {
        // TODO: Implement put() method.
    }

    public function patch(string $uri, callable|array|string $handler): void
    {
        // TODO: Implement patch() method.
    }

    public function delete(string $uri, callable|array|string $handler): void
    {
        // TODO: Implement delete() method.
    }

    public function options(string $uri, callable|array|string $handler): void
    {
        // TODO: Implement options() method.
    }
}