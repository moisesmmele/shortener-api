<?php

namespace Moises\ShortenerApi\Domain\Contracts;

use DateTimeImmutable;

interface TimestampGeneratorInterface
{
    public function generate(?string $timezone = null): DateTimeImmutable;

    public function validate(DateTimeImmutable $timestamp, ?string $timezone = null): bool;
}