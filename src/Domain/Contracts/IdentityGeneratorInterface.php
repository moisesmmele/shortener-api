<?php

namespace Moises\ShortenerApi\Domain\Contracts;

interface IdentityGeneratorInterface
{
    public function generate(): string;
    public function validate(string $identity): bool;
}