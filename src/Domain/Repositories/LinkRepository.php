<?php

namespace Moises\ShortenerApi\Domain\Repositories;

use Moises\ShortenerApi\Domain\Entities\Link;

interface LinkRepository
{
    public function save(Link $link);
    public function findByShortcode($shortcode);
    public function getAll();
}