<?php

namespace Moises\ShortenerApi\Infrastructure\Generators;

use Moises\ShortenerApi\Domain\Contracts\IdentityGeneratorInterface;

class UuidV4Generator implements IdentityGeneratorInterface
{

    public function generate(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function validate(string $identity): bool
    {
        return preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
                $identity
            ) === 1;
    }
}