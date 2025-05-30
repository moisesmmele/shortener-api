<?php

use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use Moises\ShortenerApi\Application\Contracts\UseCaseFactoryInterface;
use Moises\ShortenerApi\Application\UseCases\Factories\UseCaseFactory;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;
use Moises\ShortenerApi\Domain\Repositories\LinkRepository;
use Moises\ShortenerApi\Infrastructure\Repositories\Mongo\MongoClickRepository;
use Moises\ShortenerApi\Infrastructure\Repositories\Mongo\MongoLinkRepository;
use Moises\ShortenerApi\Infrastructure\Router\LeagueRouterAdapter;
use Moises\ShortenerApi\Infrastructure\Services\Logger\MongoLogger;
use Psr\Log\LoggerInterface;
use function DI\autowire;

return array(
    RouterInterface::class => autowire(LeagueRouterAdapter::class),
    UseCaseFactoryInterface::class => autowire(UseCaseFactory::class),
    LinkRepository::class => autowire(MongoLinkRepository::class),
    ClickRepository::class => autowire(MongoClickRepository::class),
    LoggerInterface::class => autowire(MongoLogger::class),
);