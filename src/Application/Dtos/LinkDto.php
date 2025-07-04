<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Application\Dtos;

use Moises\ShortenerApi\Domain\Entities\Link;

final class LinkDto
{
    private string $id;
    private string $longUrl;
    private string $shortcode;
    private string $createdAt;
    private ?int $ttlSeconds = null; // null means no expiration

    public function __construct(string $id, string $longUrl, string $shortcode, string $createdAt, ?int $ttlSeconds)
    {
        $this->id = $id;
        $this->longUrl = $longUrl;
        $this->shortcode = $shortcode;
        $this->createdAt = $createdAt;
        $this->ttlSeconds = $ttlSeconds;

    }

    public static function fromEntity(Link $link): self
    {
        return new self(
            $link->getId(),
            $link->getLongUrl(),
            $link->getShortcode(),
            $link->getCreatedAtString(),
            $link->getTtlSeconds()
        );
    }

    public static function fromArray(array $linkArray): self
    {
        return new self(
            $linkArray['id'],
            $linkArray['long_url'],
            $linkArray['shortcode'],
            $linkArray['created_at'],
            $linkArray['ttl_seconds']
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
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
    public function getTtlSeconds(): ?int
    {
        return $this->ttlSeconds;
    }
}
