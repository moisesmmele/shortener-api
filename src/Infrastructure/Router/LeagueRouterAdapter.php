<?php declare(strict_types=1);

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
use Psr\Log\LoggerInterface;
use function DI\string;

class LeagueRouterAdapter implements RouterInterface
{
    private Router $router;
    private LoggerInterface $logger;

    public function __construct(Router $router, Container $container, LoggerInterface $logger)
    {
        $this->router = $router;
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
        $logContext = [
            'class' => get_class($this),
            'method' => __METHOD__,
            'request' => [
                'method' => $method,
                'uri' => (string) $uri,
            ]
        ];
        try {
            $this->logger->info('resolved new request', $logContext);
            $response = $this->router->dispatch($request);
        } catch (NotFoundException $exception) {
            $this->logger->info('request to unregistered route', $logContext);
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
            $this->logger->critical('could not load routes.');
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