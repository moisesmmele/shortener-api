<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\Dtos;

use Moises\ShortenerApi\Domain\Entities\Link;

final class LinkDto
{
    private string $id;
    private string $longUrl;
    private string $shortcode;

    public function __construct(string $id, string $longUrl, string $shortcode)
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

    public function getId(): string
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
