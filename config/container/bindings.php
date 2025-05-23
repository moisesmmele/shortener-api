<?php

use Moises\ShortenerApi\Application\Contracts\Database\DatabaseInterface;
use Moises\ShortenerApi\Application\Contracts\Router\RouterInterface;
use Moises\ShortenerApi\Infrastructure\Database\SqlitePdoAdapter;
use Moises\ShortenerApi\Infrastructure\Router\LeagueRouterAdapter;
use function DI\autowire;

return array(
    RouterInterface::class => autowire(LeagueRouterAdapter::class),
    DatabaseInterface::class => autowire(SqlitePdoAdapter::class),
);