<?php

namespace Moises\ShortenerApi\Infrastructure\Generators;

use Moises\ShortenerApi\Domain\Contracts\ShortcodeGeneratorInterface;

class ShortcodeGenerator implements ShortcodeGeneratorInterface
{

    public function generate(): string
    {
        //length may be injected through environment variables later
        $length = 6;
        $string = str_replace(['+', '/', '='], '', base64_encode(random_bytes(32)));
        return substr($string, 0, $length);
    }
}