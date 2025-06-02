<?php

declare(strict_types=1);

namespace Moises\ShortenerApi\Domain\Factories;

use Moises\ShortenerApi\Domain\Entities\Link;

class LinkFactory
{
    public function generateShortLink(string $longUrl): Link
    {
        $link = new Link();
        $link->setLongUrl($longUrl);
        $link->setShortcode($this->generateShortcode(6));

        return $link;
    }

    private function generateShortcode(int $length): string
    {
        $string = str_replace(['+', '/', '='], '', base64_encode(random_bytes(32)));
        return substr($string, 0, $length);
    }
}
