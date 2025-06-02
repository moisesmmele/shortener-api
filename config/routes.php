<?php

use Laminas\Diactoros\Response\JsonResponse;
use Moises\ShortenerApi\Application\BasicMiddleware;
use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use Moises\ShortenerApi\Presentation\Http\Controllers\ClickController;
use Moises\ShortenerApi\Presentation\Http\Controllers\LinkController;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (RouterInterface $router) {
    $router->get('/', function (Request $request) {
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
        return $response;
    }, [new BasicMiddleware()]);

    $router->get('/{shortcode}', [ClickController::class, 'click']);

    $router->get('/tracker/{shortcode}', [LinkController::class, 'show'],[new BasicMiddleware()]);
    $router->post('/register/link', [LinkController::class, 'create']);
    $router->delete('/{shortcode}', [linkController::class, 'destroy']);
};