<?php

namespace Moises\ShortenerApi\Infrastructure\Generators;

use DateTimeImmutable;
use Moises\ShortenerApi\Domain\Contracts\TimestampGeneratorInterface;

class TimestampGenerator implements TimestampGeneratorInterface
{

    public function generate(?string $timezone = null): DateTimeImmutable
    {
        return new DateTimeImmutable($timezone);
    }

    public function validate(DateTimeImmutable $timestamp, ?string $timezone = null): bool
    {
        //TODO: implement
        return true;
    }
}