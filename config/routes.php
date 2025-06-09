<?php

use Laminas\Diactoros\Response\JsonResponse;
use Moises\ShortenerApi\Infrastructure\Router\RouterInterface;
use Moises\ShortenerApi\Presentation\Http\Controllers\ClickController;
use Moises\ShortenerApi\Presentation\Http\Controllers\LinkController;
use Moises\ShortenerApi\Presentation\Http\Middleware\BasicMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\CorsMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\TimeRequestMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\Validation\ClickValidationMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\Validation\LinkCreateValidationMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\Validation\LinkShowValidationMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (RouterInterface $router) {

    //global middleware to time requests
    $router->applyGlobalMiddlewares([TimeRequestMiddleware::class, CorsMiddleware::class]);

    $router->get('/', function (Request $request) {
        return new JsonResponse([
            'status' => 'OK',
            'message' => 'Hello World!',
            'hint' => 'This is a shortener API. Read more about it in the documentation: INSERT LINK HERE',
            'php_version' => PHP_VERSION,
        ]);
    });

    $router->get('/{shortcode}', [ClickController::class, 'click'], [ClickValidationMiddleware::class]);
    $router->get('/tracker/{shortcode}', [LinkController::class, 'show'], [LinkShowValidationMiddleware::class]);
    $router->post('/register/link', [LinkController::class, 'create'], [LinkCreateValidationMiddleware::class]);
    $router->delete('/{shortcode}', [linkController::class, 'destroy']);
};