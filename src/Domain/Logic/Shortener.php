<?php

namespace Moises\ShortenerApi\Domain\Logic;

use Moises\ShortenerApi\Domain\Entities\Link;

class Shortener
{
    public function generate($longUrl)
    {
        $link = new Link();
        $link->setLongUrl($longUrl);
        $string = str_replace(['+', '/', '='], '', base64_encode(random_bytes(32)));
        $link->setShortcode(substr($string, 0, 6));

        return $link;
    }
}