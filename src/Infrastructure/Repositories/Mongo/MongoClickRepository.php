<?php

namespace Moises\ShortenerApi\Infrastructure\Repositories\Mongo;

use Moises\ShortenerApi\Application\Contracts\Database\DatabaseInterface;
use Moises\ShortenerApi\Domain\Entities\Click;
use Moises\ShortenerApi\Domain\Entities\Link;
use Moises\ShortenerApi\Domain\Repositories\ClickRepository;

class MongoClickRepository implements ClickRepository
{
    private DatabaseInterface $db;
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function save(Click $click)
    {

    }

    public function findByLink(Link $link)
    {
        // TODO: Implement findByLink() method.
    }
}