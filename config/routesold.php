<?php

return function ($router) {
    $router->register(method: 'GET', uri: '/', action: function () {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => "OK",
            'status_code' => 200,
            'message' => 'Hello World!',
        ]);
    });
    $router->register(method: 'GET', uri: '/test', action: function () {
        http_response_code(200);
        header('Content-Type:text/html; charset=utf-8');
        echo "<a href='http://172.16.99.86:8083/cb14ko'>link</a>";
    });
    $router->register(method: 'POST', uri: '/register', action: [Moises\ShortenerApi\Application\ServiceHub::class, 'register']);
    $router->register(method: 'GET', uri: '/registered-links', action: [Moises\ShortenerApi\Application\ServiceHub::class, 'getAllLinks']);
    $router->register(method: 'GET', uri: '/tracker/:shortcode', action: [Moises\ShortenerApi\Application\ServiceHub::class, 'getLinkClicks']);
};