<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Entities;

class Link
{
    private int $id;
    private string $longUrl;
    private string $shortcode;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLongUrl(): string
    {
        return $this->longUrl;
    }

    public function setLongUrl(string $longUrl): void
    {
        if (strlen($longUrl) > 2048) {
            throw new \DomainException('Link is too long, maximum 2048 characters');
        }

        $sanitizedLongUrl = filter_var($longUrl, FILTER_SANITIZE_URL);
        $this->longUrl = $sanitizedLongUrl;
    }

    public function getShortcode(): string
    {
        return $this->shortcode;
    }

    public function setShortcode(string $shortcode): void
    {
        if (strlen($shortcode) !== 6) {
            throw new \DomainException('Shortcode must be exactly 6 characters');
        }
        if (!ctype_alnum($shortcode)) {
            throw new \DomainException('Shortcode must be alphanumeric');
        }

        $decapitalized = strtolower($shortcode);
        $this->shortcode = $decapitalized;
    }
}
