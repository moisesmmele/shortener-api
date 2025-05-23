<?php

use Laminas\Diactoros\Response\JsonResponse;
use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use Moises\ShortenerApi\Infrastructure\Controllers\ClickController;
use Moises\ShortenerApi\Infrastructure\Controllers\TestController;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (RouterInterface $router) {
    $router->get('/test', function (Request $request) {
        return new JsonResponse([
            'status' => 'OK',
            'message' => 'Hello World!',
            'php_version' => phpversion(),
            'request_data' => $request->getServerParams(),
        ]);
    });
    $router->get('/router', [TestController::class, 'index']);
    $router->get('/pdo', [ClickController::class, 'index']);

};