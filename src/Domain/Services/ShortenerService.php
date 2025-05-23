<?php

namespace Moises\ShortenerApi\Domain\Services;

use Moises\ShortenerApi\Domain\Entities\Link;

class ShortenerService
{
    public function generateShortLink(string $longUrl): Link
    {
        $link = new Link();
        $link->setLongUrl($longUrl);
        $link->setShortcode($this->generateShortcode(8));

        return $link;
    }

    private function generateShortcode(int $length): string
    {
        $string = str_replace(['+', '/', '='], '', base64_encode(random_bytes(32)));
        return substr($string, 0, $length);
    }
}
