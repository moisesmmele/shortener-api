<?php

namespace Moises\ShortenerApi\Infrastructure\Generators;

use DateTimeImmutable;
use DateTimeZone;
use Moises\ShortenerApi\Domain\Contracts\TimestampGeneratorInterface;

class TimestampGenerator implements TimestampGeneratorInterface
{

    public function generate(?string $timezone = null): DateTimeImmutable
    {
        if ($timezone !== null) {
            try {
                $timezoneObj = new DateTimeZone($timezone);
                return new DateTimeImmutable('now', $timezoneObj);
            } catch (\Exception) {
                // If timezone is invalid, fall back to default timezone
                return new DateTimeImmutable();
            }
        }

        return new DateTimeImmutable();
    }

    public function validate(DateTimeImmutable $timestamp, ?string $timezone = null): bool
    {
        if ($timezone !== null) {
            try {
                $expectedTimezone = new DateTimeZone($timezone);
            } catch (\Exception) {
                return false;
            }

            if ($timestamp->getTimezone()->getName() !== $expectedTimezone->getName()) {
                return false;
            }
        }

        return true;
    }
}