<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Entities;

class Link
{
    private string $id;
    private string $longUrl;
    private string $shortcode;
    private int $shortcodeMaxLength = 6;
    private \DateTimeImmutable $createdAt;
    private ?int $ttlSeconds = null; // null means no expiration

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string|\DateTimeImmutable $createdAt): void
    {
        if (is_string($createdAt)) {
            $pattern = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) ([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/';
            if (!preg_match($pattern, $createdAt)) {
                throw new \DomainException("Invalid datetime string format. Should be YYYY-MM-DD HH:MM:SS");
            }
            $createdAt = new \DateTimeImmutable($createdAt);
        }
        $this->createdAt = $createdAt;
    }

    public function getCreatedAtString(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    public function getTtlSeconds(): ?int
    {
        return $this->ttlSeconds;
    }

    public function setTtlSeconds(?int $ttlSeconds): void
    {
        if ($ttlSeconds !== null && $ttlSeconds <= 0) {
            throw new \DomainException('TTL must be a positive number of seconds or null');
        }

        $this->ttlSeconds = $ttlSeconds;
    }

    public function isValid(?\DateTimeImmutable $currentTime = null): bool
    {
        // If no TTL is set, link never expires
        if ($this->ttlSeconds === null) {
            return true;
        }

        $currentTime = $currentTime ?? new \DateTimeImmutable();
        $expiresAt = $this->createdAt->add(new \DateInterval("PT{$this->ttlSeconds}S"));

        return $currentTime <= $expiresAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        if ($this->ttlSeconds === null) {
            return null; // Never expires
        }

        return $this->createdAt->add(new \DateInterval("PT{$this->ttlSeconds}S"));
    }

    public function getTimeUntilExpiry(?\DateTimeImmutable $currentTime = null): ?\DateInterval
    {
        $expiresAt = $this->getExpiresAt();

        if ($expiresAt === null) {
            return null; // Never expires
        }

        $currentTime = $currentTime ?? new \DateTimeImmutable();

        if ($currentTime >= $expiresAt) {
            return null; // Already expired
        }

        return $currentTime->diff($expiresAt);
    }
}
