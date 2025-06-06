<?php

use Laminas\Diactoros\Response\JsonResponse;
use Moises\ShortenerApi\Application\BasicMiddleware;
use Moises\ShortenerApi\Infrastructure\Router\RouterInterface;
use Moises\ShortenerApi\Presentation\Http\Controllers\ClickController;
use Moises\ShortenerApi\Presentation\Http\Controllers\LinkController;
use Moises\ShortenerApi\Presentation\Http\Middleware\ClickValidationMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (RouterInterface $router) {
    $router->get('/test/{param}', function (Request $request) {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        $logContext = [
            'class_method' => __METHOD__,
            'request' => [
                'method' => $method,
                'path' => $path,
            ]
        ];
        $this->logger->info("[$method] [$path] 200 OK", $logContext);
        $response = new JsonResponse([
            'status' => 'OK',
            'message' => 'Hello World!',
            'php_version' => PHP_VERSION,
        ]);
        sleep(1);
        $time = new DateTimeImmutable()->format('Y-m-d H:i:s');
        return $response->withHeader('X-Time-Controller', $time);
    }, [new BasicMiddleware()]);

    $router->get('/{shortcode}', [ClickController::class, 'click'], [ClickValidationMiddleware::class]);

    $router->get('/tracker/{shortcode}', [LinkController::class, 'show'],[new BasicMiddleware()]);
    $router->post('/register/link', [LinkController::class, 'create']);
    $router->delete('/{shortcode}', [linkController::class, 'destroy']);
};