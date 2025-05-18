<?php

namespace Moises\ShortenerApi\Domain\Entities;

class Click
{
    private int $id;
    private int $linkId;
    private string $utcTimestamp;
    private string $sourceIp;
    private string $referrer;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLinkId(): int
    {
        return $this->linkId;
    }

    public function setLinkId(int $linkId): void
    {
        $this->linkId = $linkId;
    }

    public function getUtcTimestamp(): string
    {
        return $this->utcTimestamp;
    }

    public function setUtcTimestamp(string $timestamp): void
    {
        if(!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) ([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $timestamp)) {
            throw new \Exception("Invalid timestamp format. Should be YYYY-MM-DD HH:MM:SS");
        }
        $this->utcTimestamp = $timestamp;
    }

    public function generateUtcTimestamp(): void
    {
        $this->utcTimestamp = gmdate('Y-m-d H:i:s');
    }
    public function getSourceIp(): string
    {
        return $this->sourceIp;
    }

    public function setSourceIp(string $sourceIp): void
    {
        if(!preg_match('^(25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)){3}$^', $sourceIp)) {
            throw new \Exception("Invalid ip address");
        }
        $this->sourceIp = $sourceIp;
    }

    public function getReferrer(): string
    {
        return $this->referrer;
    }

    public function setReferrer(string $referrer): void
    {
        $sanitized = filter_var($referrer, FILTER_SANITIZE_URL);
        $this->referrer = $sanitized;
    }

}