<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Entities;

class Link
{
    private string $id;
    private string $longUrl;
    private string $shortcode;
    private int $shortcodeMaxLength = 6;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id)) {
            throw new \DomainException('Id should be a valid UUID-v4');
        }

        $this->id = $id;
    }

    public function getLongUrl(): string
    {
        return $this->longUrl;
    }

    public function setLongUrl(string $longUrl): void
    {
        if ($longUrl === '') {
            throw new \DomainException('Long URL was not provided.');
        }

        if (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
            throw new \DomainException("Invalid Long URL.");
        }
        $parts = parse_url($longUrl);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            throw new \DomainException("Invalid Long URL. Missing scheme or host.");
        }

        if (preg_match('/\s/', $longUrl)) {
            throw new \DomainException("Invalid Long URL. Link contains invalid characters.");
        }

        if (strlen($longUrl) > 2048) {
            throw new \DomainException('Long URL is too long, maximum 2048 characters');
        }

        $this->longUrl = $longUrl;
    }

    public function getShortcode(): string
    {
        return $this->shortcode;
    }

    public function setShortcode(string $shortcode): void
    {
        if (strlen($shortcode) !== $this->shortcodeMaxLength) {
            throw new \DomainException("Shortcode must be exactly {$this->shortcodeMaxLength} characters");
        }

        if (!ctype_alnum($shortcode)) {
            throw new \DomainException('Shortcode must be alphanumeric');
        }

        $decapitalized = strtolower($shortcode);
        $this->shortcode = $decapitalized;
    }

    public function setShortcodeMaxLength(int $length): void
    {
        $this->shortcodeMaxLength = $length;
    }

    public function getShortcodeMaxLength(): int
    {
        return $this->shortcodeMaxLength;
    }
}
