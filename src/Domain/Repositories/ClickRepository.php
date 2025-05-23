<?php

namespace Moises\ShortenerApi\Domain\Repositories;

use Moises\ShortenerApi\Domain\Entities\Click;
use Moises\ShortenerApi\Domain\Entities\Link;

interface ClickRepository
{
    public function save(Click $click);
    public function findByLink(Link $link);
}