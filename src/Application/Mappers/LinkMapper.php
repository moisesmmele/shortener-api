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
        $link->setCreatedAt($linkDto->getCreatedAt());
        return $link;
    }

    public function toDto(Link $link): LinkDto
    {
        return LinkDto::fromEntity($link);
    }

    public function toArray(Link $link): array
    {
        return [
            'id' => $link->getId(),
            'shortcode' => $link->getShortcode(),
            'long_url' => $link->getLongUrl(),
            'created_at' => $link->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}