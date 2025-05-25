<?php

namespace Moises\ShortenerApi\Application\Dtos;

use Moises\ShortenerApi\Domain\Entities\Link;

final class LinkDto
{
    private int $id;
    private string $longUrl;
    private string $shortcode;

    public function __construct(int $id, string $longUrl, string $shortcode)
    {
        $this->id = $id;
        $this->longUrl = $longUrl;
        $this->shortcode = $shortcode;
    }

    public static function fromEntity(Link $link): self
    {
        return new self(
            $link->getId(),
            $link->getLongUrl(),
            $link->getShortcode()
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLongUrl(): string
    {
        return $this->longUrl;
    }

    public function getShortcode(): string
    {
        return $this->shortcode;
    }
}
