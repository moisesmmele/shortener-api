<?php

namespace Moises\ShortenerApi\Domain\Contracts;

interface ShortcodeGeneratorInterface
{
    public function generate(): string;
}