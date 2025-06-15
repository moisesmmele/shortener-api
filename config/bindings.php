<?php

/*** @description Bindings file is responsible for declaring container bindings.
 * This is a specific implementation for DI, since it uses DI functions.
 * It returns an associative array containing FQN => concrete, which is usually a DI function.
 * This file is read by DI\ContainerBuilder::addDefinitions.
 */

use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use League\Route\Router;
use Moises\ShortenerApi\Application\UseCases\Factories\UseCaseFactory;
use Moises\ShortenerApi\Application\UseCases\UseCaseFactoryInterface;
use Moises\ShortenerApi\Domain\Contracts\IdentityGeneratorInterface;
use Moises\ShortenerApi\Domain\Contracts\ShortcodeGeneratorInterface;
use Moises\ShortenerApi\Domain\Contracts\TimestampGeneratorInterface;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Infrastructure\Cache\Memcached\MemcachedAdapter;
use Moises\ShortenerApi\Infrastructure\Cache\Memcached\MemcachedFactory;
use Moises\ShortenerApi\Infrastructure\Database\MongoAdapter;
use Moises\ShortenerApi\Infrastructure\Generators\ShortcodeGenerator;
use Moises\ShortenerApi\Infrastructure\Generators\TimestampGenerator;
use Moises\ShortenerApi\Infrastructure\Generators\UuidV4Generator;
use Moises\ShortenerApi\Infrastructure\Repositories\Mongo\MongoClickRepository;
use Moises\ShortenerApi\Infrastructure\Repositories\Mongo\MongoLinkRepository;
use Moises\ShortenerApi\Infrastructure\Router\LeagueRouterAdapter;
use Moises\ShortenerApi\Infrastructure\Router\RouterInterface;
use Moises\ShortenerApi\Infrastructure\Services\Logger\MongoLogger;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use function DI\autowire;
use function DI\factory;

return array(

    //Database and repositories
    MongoAdapter::class => factory(function () {
        return new MongoAdapter(
            host: $_ENV['DB_HOST'],
            port: $_ENV['DB_PORT'],
            username: $_ENV['DB_USER'],
            password: $_ENV['DB_PASSWORD']);
    }),

    // Cache
    Memcached::class => factory(MemcachedFactory::create(server: $_ENV['MEMCACHED_HOST'], port: $_ENV['MEMCACHED_PORT'])),
    CacheInterface::class => autowire(MemcachedAdapter::class),

    // Logger
    LoggerInterface::class => autowire(MongoLogger::class),
    //obs: autowire generate singletons in PHP-DI

    // Http
    ResponseInterface::class => autowire(Laminas\Diactoros\Response::class),
    ResponseFactoryInterface::class => autowire(ResponseFactory::class),
    ServerRequestFactoryInterface::class => autowire(ServerRequestFactory::class),
    //could use autowire, this is manually done for future reference
    RouterInterface::class => factory(function (\Psr\Container\ContainerInterface $c) {
        static $routerAdapter = null;
        if ($routerAdapter === null) {
            $router = $c->get(Router::class);
            $logger = $c->get(LoggerInterface::class);
            $routerAdapter = new LeagueRouterAdapter(leagueRouter: $router, container: $c, logger: $logger);
        }
        return $routerAdapter;
    }),

    LinkRepository::class => autowire(MongoLinkRepository::class),
    ClickRepository::class => autowire(MongoClickRepository::class),

    // Generators
    IdentityGeneratorInterface::class => autowire(UuidV4Generator::class),
    TimestampGeneratorInterface::class => autowire(TimestampGenerator::class),
    ShortcodeGeneratorInterface::class => autowire(ShortcodeGenerator::class),

    // Other
    UseCaseFactoryInterface::class => autowire(UseCaseFactory::class),
);