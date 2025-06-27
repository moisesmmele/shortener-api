<?php
/** @description This file is responsible for route declaration.
 * It returns a function that receives an instance of RouterInterface in signature.
 * This file is loaded in RouterInterface::LoadRoutes()
*/

// Just here for ease of use in 'test' route declaration with callback. Not a dependency.
use Laminas\Diactoros\Response\JsonResponse;

// Router
use Moises\ShortenerApi\Infrastructure\Router\RouterInterface;

// Controllers
use Moises\ShortenerApi\Presentation\Http\Controllers\ClickController;
use Moises\ShortenerApi\Presentation\Http\Controllers\LinkController;

// Middlewares
use Moises\ShortenerApi\Presentation\Http\Middleware\CorsMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\RateLimitMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\TimeRequestMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\Validation\ClickValidationMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\Validation\LinkCreateValidationMiddleware;
use Moises\ShortenerApi\Presentation\Http\Middleware\Validation\LinkShowValidationMiddleware;

// Request
use Psr\Http\Message\ServerRequestInterface as Request;

return function (RouterInterface $router) {

    //global middleware to time requests
    $router->applyGlobalMiddlewares([TimeRequestMiddleware::class, CorsMiddleware::class, RateLimitMiddleware::class]);

    //example route declaration with a callback function.
    $router->get('/', function (Request $request) {
        return new JsonResponse([
            'status' => 'OK',
            'message' => 'Hello World!',
            'hint' => 'This is a shortener API. Read more about it in the documentation: INSERT LINK HERE',
            'php_version' => PHP_VERSION,
        ]);
    });

    // request methods are wrappers around router method map,
    // with added middleware resolving through DI container.
    // uri can be a dynamic route with variables declared using {}, value is passed as a Request Parameter
    // can be a callback, as previously demonstrated, or an array with Class string and Method string
    // Middleware should be an array of string containing middleware FQN.

    $router->get(
        uri: '/{shortcode}',
        handler: [ClickController::class, 'click'],
        middleware: [ClickValidationMiddleware::class]
    );


    // other routes
    $router->get('/tracker/{shortcode}', [LinkController::class, 'show'], [LinkShowValidationMiddleware::class]);
    $router->post('/register/link', [LinkController::class, 'create'], [LinkCreateValidationMiddleware::class]);
    $router->delete('/{shortcode}', [linkController::class, 'destroy']);
};