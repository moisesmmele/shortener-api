<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Bootstrap;

use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use Moises\ShortenerApi\Infrastructure\App;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use DI\Container;

use function DI\env;

class AppFactory
{
    public static function create(): App
    {
        self::loadEnv();
        self::setDebugMode();
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
        $dotenv = Dotenv::createImmutable(BASE_PATH . '/config/');
        $dotenv->safeload();
    }

    private static function setDebugMode(): void
    {
        if ($_ENV['APP_DEBUG'] === 'true') {
            define('APP_DEBUG', true);
        } else {
            define('APP_DEBUG', false);
        }
    }
}
