<?php

namespace Moises\ShortenerApi\Infrastructure\Bootstrap;

use DI\Container;
use DI\ContainerBuilder;
use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use Moises\ShortenerApi\Infrastructure\App;

class AppFactory
{
    public static function create(): App
    {

        $container = self::container();
        $router = $container->get(RouterInterface::class);

        return new App($router);
    }
    private static function container(): Container
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(BASE_PATH . '/config/container/bindings.php');
        return $containerBuilder->build();
    }
    private static function loadEnv(): void
    {

    }
}