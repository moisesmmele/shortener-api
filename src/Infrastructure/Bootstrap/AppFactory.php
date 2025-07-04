<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Infrastructure\Bootstrap;

use DI\Container;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Moises\ShortenerApi\Infrastructure\App;

class AppFactory
{
    public static function create(): App
    {
        try {
            if (!isset($_ENV['APP_ENV'])) {
                self::loadEnv();
            }
            self::setDebugMode();
            self::setFastCgiMode();
            $container = self::container();
            $app = $container->make(App::class);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            exit(1);
        }

        return $app;
    }
    public static function container(): Container
    {
        $containerBuilder = new ContainerBuilder();
        $path = BASE_PATH . '/config/bindings.php';
        $containerBuilder->addDefinitions($path);
        return $containerBuilder->build();
    }
    public static function loadEnv(): void
    {
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        try {
            $dotenv->load();
        } catch (\Throwable $exception) {
            error_log('[error]: env not found: ' . $exception->getMessage());
            error_log('[critical]: cannot continue.');
            exit(1);
        }
    }

    public static function setDebugMode(): void
    {
        if ($_ENV['APP_DEBUG'] === 'true') {
            define('APP_DEBUG', true);
            error_reporting(E_ALL);
            error_log('[warning]: Running in debug mode.');
        } else {
            define('APP_DEBUG', false);
        }
    }
    public static function setFastCgiMode(): void
    {
        $isFastCgi = function_exists('fastcgi_finish_request');
        if ($isFastCgi) {
            define('IS_FASTCGI', true);
        } else {
            define('IS_FASTCGI', false);
            error_log('[warning]: You are not running in FastCGI mode. task execution will affect TTFB.');
        }
    }
}
