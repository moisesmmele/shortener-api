<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Factories;

use Moises\ShortenerApi\Domain\Contracts\IdentityGeneratorInterface;
use Moises\ShortenerApi\Domain\Contracts\ShortcodeGeneratorInterface;
use Moises\ShortenerApi\Domain\Contracts\TimestampGeneratorInterface;
use Moises\ShortenerApi\Domain\Entities\Link;

class LinkFactory
{
    public function __construct(
        private ShortcodeGeneratorInterface $shortcodeGenerator,
        private IdentityGeneratorInterface $identityGenerator,
        private TimestampGeneratorInterface $timestampGenerator
    )
    {}

    public function create(string $longUrl): Link
    {
        $link = new Link();
        $link->setId($this->identityGenerator->generate());
        $link->setShortcode($this->shortcodeGenerator->generate());
        $link->setCreatedAt($this->timestampGenerator->generate());
        $link->setLongUrl($longUrl);
        $link->setTtlSeconds(null);

        return $link;
    }
}
