<?php

namespace Moises\ShortenerApi\Application\Mappers;

use Moises\ShortenerApi\Application\Dtos\LinkDto;
use Moises\ShortenerApi\Domain\Entities\Link;

class LinkMapper
{
    public function FromDto(LinkDto $linkDto): Link
    {
        $link = new Link();
        $link->setId($linkDto->getId());
        $link->setShortcode($linkDto->getShortcode());
        $link->setLongUrl($linkDto->getLongUrl());
        $link->setShortcodeMaxLength(strlen($linkDto->getShortcode()));
        return $link;
    }

    public function toDto(Link $link): LinkDto
    {
        return LinkDto::fromEntity($link);
    }
}