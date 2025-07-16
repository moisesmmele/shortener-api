<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Repositories;

use Moises\ShortenerApi\Domain\Entities\Link;

interface LinkRepository
{
    public function save(Link $link): Link;

    public function findByShortcode(string $shortcode): ?Link;

    /** @return Link[] */
    public function getAll(): array;

    public function delete(Link $link): void;
}
