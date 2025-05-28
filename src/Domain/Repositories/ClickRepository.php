<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Repositories;

use Moises\ShortenerApi\Domain\Entities\Click;
use Moises\ShortenerApi\Domain\Entities\Link;

interface ClickRepository
{
    public function save(Click $click): Click;

    /** @return Click[] */
    public function findByLink(Link $link): array;
}
