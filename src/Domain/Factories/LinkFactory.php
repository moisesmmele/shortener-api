<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Factories;

use Moises\ShortenerApi\Domain\Contracts\ShortcodeGeneratorInterface;
use Moises\ShortenerApi\Domain\Entities\Link;

class LinkFactory
{
    public function __construct(private ShortcodeGeneratorInterface $shortcodeGenerator)
    {}

    public function generateShortLink(string $longUrl): Link
    {
        $link = new Link();
        $link->setLongUrl($longUrl);
        $link->setShortcode($this->shortcodeGenerator->generate());

        return $link;
    }
}
